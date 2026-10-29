FROM ubuntu:22.04

# Install FFmpeg and Java
RUN apt-get update && apt-get install -y \
    ffmpeg \
    openjdk-21-jdk-headless \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy compiled Java app
COPY build/StreamingApp.jar /app/streaming.jar

# Copy any config files
COPY config/ /app/config/

EXPOSE 8080 1935

# Run the Java app
CMD ["java", "-jar", "streaming.jar"]