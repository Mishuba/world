# syntax=docker/dockerfile:1

# ---- .NET SDK source (copied into the final image) ----
FROM mcr.microsoft.com/dotnet/sdk:8.0 AS dotnet

# ---- Final image: PHP + nginx + .NET + Java + Python ----
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor gettext-base curl libpq-dev \
        git unzip ffmpeg \
        openjdk-17-jdk-headless \
        python3 python3-pip python3-venv \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

# .NET (C# and VB.NET)
COPY --from=dotnet /usr/share/dotnet /usr/share/dotnet
RUN ln -s /usr/share/dotnet/dotnet /usr/bin/dotnet
ENV DOTNET_CLI_TELEMETRY_OPTOUT=1 \
    DOTNET_NOLOGO=1 \
    DOTNET_SYSTEM_GLOBALIZATION_INVARIANT=1

# Python virtualenv + requirements
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
COPY Python/requirements.txt /tmp/requirements.txt
RUN pip install --no-cache-dir -r /tmp/requirements.txt || echo "pip install failed - check Python/requirements.txt"

# PHP: let PHP see Render environment variables
RUN printf "[www]\nclear_env = no\n" > /usr/local/etc/php-fpm.d/zz-env.conf
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer config --global audit.block-insecure false \
    && composer install --no-dev --prefer-dist --no-scripts --no-interaction --optimize-autoloader || true

COPY . .

# Build your own programs (a failure is logged but won't break the deploy)
RUN dotnet build "C#/c#.csproj" -c Release || echo "C# build failed"
RUN dotnet build "vb.net/vb.net.vbproj" -c Release || echo "VB.NET build failed"
RUN mkdir -p /opt/build/java \
    && find Java -name "*.java" > /tmp/java-files.txt \
    && if [ -s /tmp/java-files.txt ]; then javac -d /opt/build/java @/tmp/java-files.txt || echo "Java build failed"; fi

# =====================================================================
# Web services: one per language, each on its own port.
# Each one runs YOUR existing program and returns its output over HTTP.
# They live in /opt/services, so nothing new is added to your repo.
#   Python -> 5001   C# -> 5002   VB.NET -> 5003   Java -> 5004
# =====================================================================

# ---- Python service (port 5001) ----
COPY <<"PYEOF" /opt/services/python/server.py
import os
import subprocess
from http.server import BaseHTTPRequestHandler, HTTPServer

SCRIPT = os.environ.get("PY_SCRIPT", "Syntax.py")

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        r = subprocess.run(
            ["python3", SCRIPT],
            cwd="/var/www/html/Python",
            capture_output=True, text=True, timeout=60,
        )
        body = (r.stdout + r.stderr).encode()
        self.send_response(200)
        self.send_header("Content-Type", "text/plain; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

HTTPServer(("127.0.0.1", 5001), Handler).serve_forever()
PYEOF

# ---- C# service (port 5002) ----
COPY <<"CSPROJEOF" /opt/services/csharp/csserver.csproj
<Project Sdk="Microsoft.NET.Sdk.Web">
  <PropertyGroup>
    <TargetFramework>net8.0</TargetFramework>
    <AssemblyName>csserver</AssemblyName>
    <Nullable>enable</Nullable>
    <ImplicitUsings>enable</ImplicitUsings>
  </PropertyGroup>
</Project>
CSPROJEOF

COPY <<"CSEOF" /opt/services/csharp/Program.cs
using System.Diagnostics;

var app = WebApplication.CreateBuilder(args).Build();

app.MapGet("/", async () =>
{
    var psi = new ProcessStartInfo("dotnet",
        "run --project \"/var/www/html/C#/c#.csproj\" -c Release --no-build")
    {
        RedirectStandardOutput = true,
        RedirectStandardError = true
    };
    using var p = Process.Start(psi)!;
    var o = await p.StandardOutput.ReadToEndAsync();
    var e = await p.StandardError.ReadToEndAsync();
    await p.WaitForExitAsync();
    return Results.Text(o + e);
});

app.Run();
CSEOF

# ---- VB.NET service (port 5003) ----
COPY <<"VBPROJEOF" /opt/services/vbnet/vbserver.vbproj
<Project Sdk="Microsoft.NET.Sdk">
  <PropertyGroup>
    <OutputType>Exe</OutputType>
    <TargetFramework>net8.0</TargetFramework>
    <AssemblyName>vbserver</AssemblyName>
    <RootNamespace>VbServer</RootNamespace>
  </PropertyGroup>
</Project>
VBPROJEOF

COPY <<"VBEOF" /opt/services/vbnet/Program.vb
Imports System.Net
Imports System.Diagnostics

Module Server
    Sub Main()
        Dim listener As New HttpListener()
        listener.Prefixes.Add("http://127.0.0.1:5003/")
        listener.Start()
        While True
            Dim ctx = listener.GetContext()
            Dim psi As New ProcessStartInfo("dotnet", "run --project ""/var/www/html/vb.net/vb.net.vbproj"" -c Release --no-build")
            psi.RedirectStandardOutput = True
            psi.RedirectStandardError = True
            Dim p = Process.Start(psi)
            Dim output = p.StandardOutput.ReadToEnd() & p.StandardError.ReadToEnd()
            p.WaitForExit()
            Dim bytes = System.Text.Encoding.UTF8.GetBytes(output)
            ctx.Response.ContentType = "text/plain; charset=utf-8"
            ctx.Response.OutputStream.Write(bytes, 0, bytes.Length)
            ctx.Response.Close()
        End While
    End Sub
End Module
VBEOF

RUN dotnet build /opt/services/csharp/csserver.csproj -c Release -o /opt/services/csharp/out \
    && dotnet build /opt/services/vbnet/vbserver.vbproj -c Release -o /opt/services/vbnet/out

# ---- Java service (port 5004) ----
COPY <<"JAVAEOF" /opt/services/java/Server.java
import com.sun.net.httpserver.HttpServer;
import java.net.InetSocketAddress;

public class Server {
    public static void main(String[] args) throws Exception {
        String mainClass = System.getenv().getOrDefault("JAVA_MAIN", "Main");
        HttpServer server = HttpServer.create(new InetSocketAddress("127.0.0.1", 5004), 0);
        server.createContext("/", exchange -> {
            Process p = new ProcessBuilder("java", "-cp", "/opt/build/java", mainClass)
                    .redirectErrorStream(true).start();
            byte[] body = p.getInputStream().readAllBytes();
            exchange.getResponseHeaders().add("Content-Type", "text/plain; charset=utf-8");
            exchange.sendResponseHeaders(200, body.length);
            exchange.getResponseBody().write(body);
            exchange.close();
        });
        server.start();
    }
}
JAVAEOF

# ---- Config files ----
COPY default.conf.template /etc/nginx/templates/default.conf.template
COPY supervisord.conf /etc/supervisord.conf
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && mkdir -p /run/nginx /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html/public

# Render injects PORT; default for local use
ENV PORT=10000
EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=5s CMD curl -fs http://127.0.0.1:${PORT}/health.php || exit 1

ENTRYPOINT ["/entrypoint.sh"]