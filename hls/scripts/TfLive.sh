#!/bin/bash

STREAM_KEY="$1"
INPUT_STREAM="rtmp://localhost/live/$STREAM_KEY"
LOGFILE="/var/log/ffmpeg-restream.log"

YOUTUBE_URL="rtmp://a.rtmp.youtube.com/live2/3egr-4vfq-56yj-amtg-e7v1"
# TWITCH_URL="rtmp://live.twitch.tv/app/XXXX"
# INSTAGRAM_URL="rtmp://rtmp.instagram.com:80/rtmp/XXXX"

echo "$(date) Starting enhanced FFmpeg for stream: $STREAM_KEY" >> "$LOGFILE"

/usr/bin/ffmpeg -loglevel warning \
-fflags nobuffer \
-flags low_delay \
-i "$INPUT_STREAM" \
-vf "fps=30,minterpolate=fps=60,hqdn3d=1.5:1.5:6:6,scale=1280:720:flags=lanczos,unsharp=5:5:0.8:3:3:0.4,eq=contrast=1.1:saturation=1.15" \
-c:v libx264 -preset veryfast -tune zerolatency \
-x264-params "nal-hrd=cbr:force-cfr=1"
-b:v 3000k -minrate 2000k -maxrate 4000k -bufsize 8000k
-g 60 -keyint_min 60 \
-c:a aac -b:a 128k \
-f flv "$YOUTUBE_URL" \
>> "$LOGFILE" 2>&1

echo "$(date) FFmpeg exited for stream: $STREAM_KEY" >> "$LOGFILE"