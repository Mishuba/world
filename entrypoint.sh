#!/bin/sh
set -e

# Render provides $PORT; fall back for local runs
export PORT="${PORT:-10000}"

# Fill in only ${PORT}, leaving nginx's own $variables untouched
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Validate nginx config before starting
nginx -t

# Start php-fpm + nginx + language services under supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf