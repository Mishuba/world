#!/bin/bash

STREAM_KEY="$1"
INPUT_STREAM="rtmp://localhost/live/$STREAM_KEY"
LOGFILE="/var/log/ffmpeg-restream.log"

YOUTUBE_URL="rtmp://a.rtmp.youtube.com/live2/3egr-4vfq-56yj-amtg-e7v1"
# TWITCH_URL="rtmp://live.twitch.tv/app/XXXX"
# INSTAGRAM_URL="rtmp://rtmp.instagram.com:80/rtmp/XXXX"

echo "$(date) Starting FFmpeg for stream: $STREAM_KEY" >> "$LOGFILE"

/usr/bin/ffmpeg -loglevel warning -i "$INPUT_STREAM" \
  -c:v copy -c:a copy \
  -f flv "$YOUTUBE_URL" \
  >> "$LOGFILE" 2>&1

echo "$(date) FFmpeg exited for stream: $STREAM_KEY" >> "$LOGFILE"