# placeholder

#!/bin/bash

INPUT_STREAM="rtmp://localhost/live/$1"   # dynamic stream name from Nginx exec_static
LOGFILE="/var/log/ffmpeg-restream.log"

# Targets
# TWITCH_URL="rtmp://live.twitch.tv/app/live166160210ZHhBDWe1EEdfcgna30jo7srMmhWIiF"
YOUTUBE_URL="rtmp://a.rtmp.youtube.com/live2/3egr-4vfq-56yj-amtg-e7v1"
# INSTAGRAM_URL="rtmp://rtmp.instagram.com:80/rtmp/$INSTAGRAM_KEY"

echo "$(date) Starting FFmpeg restream for stream: $1" >> "$LOGFILE"

# Run FFmpeg
/usr/bin/ffmpeg -i "$INPUT_STREAM" \
    -c:v copy -c:a copy -f flv "$YOUTUBE_URL" \
    >> "$LOGFILE" 2>&1

#   #  -c:v copy -c:a copy -f flv "$TWITCH_URL" \
# -c:v copy -c:a copy -f flv "$INSTAGRAM_URL" \

echo "$(date) FFmpeg exited for stream: $1" >> "$LOGFILE"
