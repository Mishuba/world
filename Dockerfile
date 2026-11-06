# syntax=docker/dockerfile:1
# ---- .NET SDK source (copied into the final image) ----
FROM mcr.microsoft.com/dotnet/sdk:8.0 AS dotnet

# ---- Final image: PHP + nginx + .NET + Java + Python ----
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor gettext-base curl libpq-dev \
    git unzip ffmpeg maven \
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
    && (mvn -B -q -f Java/mae/pom.xml -DskipTests package \
    && cp Java/mae/target/app.jar /opt/build/java/app.jar \
    || echo "Java build failed")
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
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import urlparse, parse_qs

SCRIPT = os.environ.get("PY_SCRIPT", "Syntax.py")

class Handler(BaseHTTPRequestHandler):
    def run_it(self):
        args = parse_qs(urlparse(self.path).query).get("arg", [])
        length = int(self.headers.get("Content-Length") or 0)
        stdin = self.rfile.read(length).decode() if length else None
        try:
            r = subprocess.run(
                ["python3", SCRIPT, *args],
                cwd="/var/www/html/Python",
                input=stdin, capture_output=True, text=True, timeout=60,
            )
            status = 200 if r.returncode == 0 else 500
            body = (r.stdout + r.stderr).encode()
        except subprocess.TimeoutExpired:
            status, body = 504, b"timeout"
        self.send_response(status)
        self.send_header("Content-Type", "text/plain; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    do_GET = run_it
    do_POST = run_it

ThreadingHTTPServer(("127.0.0.1", 5001), Handler).serve_forever()

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

app.MapMethods("/", new[] { "GET", "POST" }, async (HttpRequest req) =>
{
    var psi = new ProcessStartInfo("dotnet")
    {
        RedirectStandardOutput = true,
        RedirectStandardError = true,
        RedirectStandardInput = true
    };
    foreach (var a in new[] { "run", "--project", "/var/www/html/C#/c#.csproj", "-c", "Release", "--no-build", "--" })
        psi.ArgumentList.Add(a);
    foreach (var a in req.Query["arg"])
        psi.ArgumentList.Add(a!);

    using var p = Process.Start(psi)!;
    using var reader = new StreamReader(req.Body);
    var body = await reader.ReadToEndAsync();
    var oTask = p.StandardOutput.ReadToEndAsync();
    var eTask = p.StandardError.ReadToEndAsync();
    if (body.Length > 0) await p.StandardInput.WriteAsync(body);
    p.StandardInput.Close();

    using var cts = new CancellationTokenSource(TimeSpan.FromSeconds(60));
    try { await p.WaitForExitAsync(cts.Token); }
    catch (OperationCanceledException)
    {
        p.Kill(true);
        return Results.Text("timeout", statusCode: 504);
    }
    return Results.Text(await oTask + await eTask, statusCode: p.ExitCode == 0 ? 200 : 500);
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
Imports System.IO
Imports System.Threading

Module Server
    Sub Main()
        Dim listener As New HttpListener()
        listener.Prefixes.Add("http://127.0.0.1:5003/")
        listener.Start()
        While True
            Dim ctx = listener.GetContext()
            ThreadPool.QueueUserWorkItem(AddressOf HandleRequest, ctx)
        End While
    End Sub

    Sub HandleRequest(state As Object)
        Dim ctx = DirectCast(state, HttpListenerContext)
        Dim status As Integer = 200
        Dim output As String = ""
        Try
            Dim psi As New ProcessStartInfo("dotnet")
            psi.RedirectStandardOutput = True
            psi.RedirectStandardError = True
            psi.RedirectStandardInput = True
            For Each a In {"run", "--project", "/var/www/html/vb.net/vb.net.vbproj", "-c", "Release", "--no-build", "--"}
                psi.ArgumentList.Add(a)
            Next
            Dim vals = ctx.Request.QueryString.GetValues("arg")
            If vals IsNot Nothing Then
                For Each a In vals
                    psi.ArgumentList.Add(a)
                Next
            End If

            Dim body As String = ""
            If ctx.Request.HasEntityBody Then
                Using reader As New StreamReader(ctx.Request.InputStream, ctx.Request.ContentEncoding)
                    body = reader.ReadToEnd()
                End Using
            End If

            Using p = Process.Start(psi)
                Dim oTask = p.StandardOutput.ReadToEndAsync()
                Dim eTask = p.StandardError.ReadToEndAsync()
                If body.Length > 0 Then p.StandardInput.Write(body)
                p.StandardInput.Close()
                If Not p.WaitForExit(60000) Then
                    p.Kill(True)
                    status = 504
                    output = "timeout"
                Else
                    output = oTask.Result & eTask.Result
                    If p.ExitCode <> 0 Then status = 500
                End If
            End Using
        Catch ex As Exception
            status = 500
            output = ex.Message
        End Try

        Dim bytes = System.Text.Encoding.UTF8.GetBytes(output)
        ctx.Response.StatusCode = status
        ctx.Response.ContentType = "text/plain; charset=utf-8"
        ctx.Response.OutputStream.Write(bytes, 0, bytes.Length)
        ctx.Response.Close()
    End Sub
End Module
VBEOF

RUN dotnet build /opt/services/csharp/csserver.csproj -c Release -o /opt/services/csharp/out \
    && dotnet build /opt/services/vbnet/vbserver.vbproj -c Release -o /opt/services/vbnet/out

# ---- Java service (port 5004) ----
COPY <<"JAVAEOF" /opt/services/java/Server.java
import com.sun.net.httpserver.HttpServer;
import java.net.InetSocketAddress;
import java.net.URLDecoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.CompletableFuture;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

public class Server {
    public static void main(String[] args) throws Exception {
        HttpServer server = HttpServer.create(new InetSocketAddress("127.0.0.1", 5004), 0);
        server.setExecutor(Executors.newCachedThreadPool());
        server.createContext("/", exchange -> {
            int status = 200;
            byte[] out;
            try {
                List<String> cmd = new ArrayList<>(List.of("java", "-jar", "/opt/build/java/app.jar"));
                String q = exchange.getRequestURI().getRawQuery();
                if (q != null) {
                    for (String pair : q.split("&")) {
                        int i = pair.indexOf('=');
                        if (i < 0) continue;
                        String k = URLDecoder.decode(pair.substring(0, i), StandardCharsets.UTF_8);
                        if (k.equals("arg"))
                            cmd.add(URLDecoder.decode(pair.substring(i + 1), StandardCharsets.UTF_8));
                    }
                }
                byte[] in = exchange.getRequestBody().readAllBytes();
                Process p = new ProcessBuilder(cmd).redirectErrorStream(true).start();
                CompletableFuture<byte[]> result = CompletableFuture.supplyAsync(() -> {
                    try { return p.getInputStream().readAllBytes(); }
                    catch (Exception e) { return new byte[0]; }
                });
                try (var os = p.getOutputStream()) { os.write(in); } catch (Exception ignored) {}
                if (!p.waitFor(60, TimeUnit.SECONDS)) {
                    p.destroyForcibly();
                    status = 504;
                    out = "timeout".getBytes(StandardCharsets.UTF_8);
                } else {
                    out = result.get(5, TimeUnit.SECONDS);
                    if (p.exitValue() != 0) status = 500;
                }
            } catch (Exception e) {
                status = 500;
                out = String.valueOf(e.getMessage()).getBytes(StandardCharsets.UTF_8);
            }
            exchange.getResponseHeaders().add("Content-Type", "text/plain; charset=utf-8");
            exchange.sendResponseHeaders(status, out.length);
            exchange.getResponseBody().write(out);
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