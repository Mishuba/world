#!/bin/sh
export PORT=${PORT:-10000}
envsubst '${PORT}' < /etc/nginx/default.conf.template > /etc/nginx/conf.d/default.conf
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf