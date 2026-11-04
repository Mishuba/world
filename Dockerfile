# FROM ubuntu:22.04
FROM php:8.3.36-cli

RUN apt-get update \
    && apt-get install -y \
    git \
    unzip \
    ffmpeg \
    openjdk-21-jdk-headless \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./

# Copy compiled Java app
# COPY build/StreamingApp.jar /app/Java/Main.jar

RUN composer config --global audit.block-insecure false \
    && composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

COPY . .

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]

# Run the Java app
# CMD ["java", "-jar", "streaming.jar"]
