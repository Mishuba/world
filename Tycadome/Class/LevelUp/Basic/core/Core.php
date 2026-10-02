<?php
require_once __DIR__ . "/core/Tycadome.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;

class BasicServer extends TycadomeServer implements MessageComponentInterface
{

    public function RadioPlaylist($reType, $accessKey, $secretKey, $r2Endpoint, $sentToJsArray)
    {
        if ($reType !== 'fetchRadioSongs') {
            http_response_code(400);
            //respond(["error" => "Invalid Request Type"]);
            exit;
        } else {
            if (!$accessKey || !$secretKey || !$r2Endpoint) {
                // If credentials are missing, respond with an error instead of silently failing
                http_response_code(500);
                //respond([        "error" => "Missing R2 credentials or endpoint. Set R2_ACCESS_KEY, R2_SECRET_KEY and R2_ENDPOINT environment variables."    ]);
                exit;
            }

            // 12 a.m Rizz IceBreakers Flirting GetHerDone Shot
            addSongsToArray("Music/Rizz/IceBreakers/", $sentToJsArray, 0, 0);
            addSongsToArray("Music/Rizz/Flirting/", $sentToJsArray, 0, 1);
            addSongsToArray("Music/Rizz/GetHerDone/", $sentToJsArray, 0, 2);
            addSongsToArray("Music/Rizz/Shots/", $sentToJsArray, 0, 3);

            // 1 a.m.
            addSongsToArray("Music/Dance/Twerking/", $sentToJsArray, 1, 0);
            addSongsToArray("Music/Dance/LineDance/", $sentToJsArray, 1, 1);
            addSongsToArray("Music/Dance/PopDance/", $sentToJsArray, 1, 2);
            addSongsToArray("Music/Dance/Battle/", $sentToJsArray, 1, 3);

            // 2 a.m
            addSongsToArray("Music/Afterparty/", $sentToJsArray, 2, null);

            // 3 a.m Sex
            addSongsToArray("Music/Sex/Foreplay/", $sentToJsArray, 3, 0);
            addSongsToArray("Music/Sex/sex/", $sentToJsArray, 3, 1);
            addSongsToArray("Music/Sex/Cuddle/", $sentToJsArray, 3, 2);

            // 4 a.m Love 
            addSongsToArray("Music/Love/Memories/", $sentToJsArray, 4, 0);
            addSongsToArray("Music/Love/love/", $sentToJsArray, 4, 1);
            addSongsToArray("Music/Love/Intimacy/", $sentToJsArray, 4, 2);

            // 5 a.m Family
            addSongsToArray("Music/Family/Lifestyle/", $sentToJsArray, 5, 0);
            addSongsToArray("Music/Family/Values/", $sentToJsArray, 5, 1);
            addSongsToArray("Music/Family/Kids/", $sentToJsArray, 5, 2);

            // 6 a.m Inspiration
            addSongsToArray("Music/Inspiration/Motivation/", $sentToJsArray, 6, 0);
            addSongsToArray("Music/Inspiration/Meditation/", $sentToJsArray, 6, 1);
            addSongsToArray("Music/Inspiration/Something/", $sentToJsArray, 6, 2);

            // 7 a.m History 
            addSongsToArray("Music/History/DH/", $sentToJsArray, 7, 0);
            addSongsToArray("Music/History/BAH/", $sentToJsArray, 7, 1);
            addSongsToArray("Music/History/HFnineteen/", $sentToJsArray, 7, 2);

            // 8 a.m Politics 
            addSongsToArray("Music/Politics/Neutral/", $sentToJsArray, 8, 0);
            addSongsToArray("Music/Politics/Democracy/", $sentToJsArray, 8, 1);
            addSongsToArray("Music/Politics/Republican/", $sentToJsArray, 8, 2);
            addSongsToArray("Music/Politics/Socialism/", $sentToJsArray, 8, 3);
            addSongsToArray("Music/Politics/Bureaucracy/", $sentToJsArray, 8, 4);
            addSongsToArray("Music/Politics/Aristocratic/", $sentToJsArray, 8, 5);

            // 9 a.m. Gaming 
            addSongsToArray("Music/Gaming/Fighters/", $sentToJsArray, 9, 0);
            addSongsToArray("Music/Gaming/Shooters/", $sentToJsArray, 9, 1);
            addSongsToArray("Music/Gaming/Instrumentals/", $sentToJsArray, 9, 2);

            // 10 a.m. Comedy
            addSongsToArray("Music/Comedy/", $sentToJsArray, 10, null);

            // 12 a.m Literature
            addSongsToArray("Music/Literature/Poems/", $sentToJsArray, 12, 0);
            addSongsToArray("Music/Literature/SS/", $sentToJsArray, 12, 1);
            addSongsToArray("Music/Literature/Instrumentals/", $sentToJsArray, 12, 2);
            addSongsToArray("Music/Literature/Books/", $sentToJsArray, 12, 3);

            // 1 p.m Sports 
            addSongsToArray("Music/Sports/Reviews/", $sentToJsArray, 13, 0);
            addSongsToArray("Music/Sports/Fans/", $sentToJsArray, 13, 1);
            addSongsToArray("Music/Sports/Updates/", $sentToJsArray, 13, 2);
            addSongsToArray("Music/Sports/Disses/", $sentToJsArray, 13, 3);

            // 2 p.m Tech
            addSongsToArray("Music/Tech/News/", $sentToJsArray, 14, 0);
            addSongsToArray("Music/Tech/Music/", $sentToJsArray, 14, 1);
            addSongsToArray("Music/Tech/History/", $sentToJsArray, 14, 2);
            addSongsToArray("Music/Tech/Instrumentals/", $sentToJsArray, 14, 3);

            // 3 p.m Science 
            addSongsToArray("Music/Science/Biology/", $sentToJsArray, 15, 0);
            addSongsToArray("Music/Science/Chemistry/", $sentToJsArray, 15, 1);
            addSongsToArray("Music/Science/Physics/", $sentToJsArray, 15, 2);
            addSongsToArray("Music/Science/Environmental/", $sentToJsArray, 15, 3);

            // 4 p.m Real Estate EP, Seller, Mortgage, Buyer
            addSongsToArray("Music/RealEstate/EP/", $sentToJsArray, 16, 0);
            addSongsToArray("Music/RealEstate/Seller/", $sentToJsArray, 16, 1);
            addSongsToArray("Music/RealEstate/Mortgage/", $sentToJsArray, 16, 2);
            addSongsToArray("Music/RealEstate/Buyer/", $sentToJsArray, 16, 3);

            // 5 p.m DJ Shuba (Basically New Music)
            addSongsToArray("Music/DJshuba/", $sentToJsArray, 17, null);

            // 6 p.m Film
            addSongsToArray("Music/Film/NM/", $sentToJsArray, 18, 0);
            addSongsToArray("Music/Film/SHM/", $sentToJsArray, 18, 1);
            addSongsToArray("Music/Film/MH/", $sentToJsArray, 18, 2);
            addSongsToArray("Music/Film/VM/", $sentToJsArray, 18, 3);
            addSongsToArray("Music/Film/LS/", $sentToJsArray, 18, 4);
            addSongsToArray("Music/Film/CB/", $sentToJsArray, 18, 5);

            // 7 p.m Fashion PD, LD, FH, SM 
            addSongsToArray("Music/Fashion/PD/", $sentToJsArray, 19, 0);
            addSongsToArray("Music/Fashion/LD/", $sentToJsArray, 19, 1);
            addSongsToArray("Music/Fashion/FH/", $sentToJsArray, 19, 2);
            addSongsToArray("Music/Fashion/SM/", $sentToJsArray, 19, 3);

            // 8 p.m Business 
            addSongsToArray("Music/Business/FE/", $sentToJsArray, 20, 0);
            addSongsToArray("Music/Business/TOB/", $sentToJsArray, 20, 1);
            addSongsToArray("Music/Business/Insurance/", $sentToJsArray, 20, 2);
            addSongsToArray("Music/Business/TE/", $sentToJsArray, 20, 3);

            // Hustlin
            addSongsToArray("Music/Hustlin/", $sentToJsArray, 21, null);

            // Pregame
            addSongsToArray("Music/Pregame/", $sentToJsArray, 22, null);

            // Outside
            addSongsToArray("Music/Outside/", $sentToJsArray, 23, null);


            // --- Finally output JSON ---
            $sentToJsArray[11] = array_values(array_unique($sentToJsArray[11]));
            $TsunamiFlowRadio = json_encode($sentToJsArray, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_SLASHES);
            echo TycadomeServer::respond($sentToJsArray);
            exit;
        }
    }
    public function onOpen(ConnectionInterface $conn)
    {
        $origin = $conn->httpRequest->getHeader('Origin')[0] ?? '';
        if ($origin !== 'https://tsunamiflow.club') {
            $conn->close();
            return;
        }

        $this->clients->attach($conn);

        parse_str($conn->httpRequest->getUri()->getQuery(), $params);

        $conn->meta = [
            'role' => $params['role'] ?? 'viewer',
            'key' => $params['key'] ?? null
        ];

        echo "🟢 {$conn->resourceId} connected ({$conn->meta['role']})\n";

        // If a streamer joins, start restream if not already running
        if ($conn->meta['role'] === 'streamer') {

        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $key = $from->meta['key'] ?? null;
        if (!$key)
            return;

        // Binary video from client
        if (!is_string($msg)) {
            if (!isset($this->ffmpeg[$key]))
                return;

            $bytes = @fwrite($this->ffmpeg[$key]['stdin'], $msg);
            if ($bytes === false) {
                echo "⚠️ FFmpeg pipe broken for $key, stopping stream\n";
                $this->stopFFmpeg($from);
            }
            return;
        }
        // JSON control messages
        $data = json_decode($msg, true);
        if (!isset($data['type']))
            return;

        switch ($data['type']) {
            case 'start_stream':
                $this->startFFmpeg($from);
                break;

            case 'stop_stream':
                $this->stopFFmpeg($from);
                break;
        }
    }

    protected function startFFmpeg(ConnectionInterface $conn)
    {
        $key = $conn->meta['key'];
        if (!$key || isset($this->ffmpeg[$key]))
            return;

        echo "🚀 Starting FFmpeg for local RTMP push: $key\n";

        $cmd = [
            'ffmpeg',
            '-loglevel',
            'warning',
            '-fflags',
            'nobuffer',
            '-flags',
            'low_delay',
            '-analyzeduration',
            '0',
            '-probesize',
            '32',
            '-f',
            'webm',
            '-thread_queue_size',
            '512',   // buffer stdin bursts
            '-i',
            'pipe:0',
            '-map',
            '0:v:0',
            '-map',
            '0:a:0',
            '-c:v',
            'libx264',
            '-preset',
            'veryfast',
            '-tune',
            'zerolatency',
            '-profile:v',
            'baseline',      // RTMP/CDN friendly
            '-pix_fmt',
            'yuv420p',
            '-g',
            '60',                     // keyframe every 2s @30fps
            '-keyint_min',
            '60',
            '-c:a',
            'aac',
            '-ar',
            '48000',
            '-b:a',
            '128k',
            '-f',
            'flv',
            "rtmp://localhost/live/$key"
        ];

        $desc = [
            0 => ['pipe', 'w'], // stdin
            1 => ['file', "/tmp/ffmpeg-$key.log", 'a'],
            2 => ['file', "/tmp/ffmpeg-$key.err", 'a']
        ];

        $proc = proc_open($cmd, $desc, $pipes);
        if (!is_resource($proc))
            return;

        stream_set_blocking($pipes[0], false);

        $this->ffmpeg[$key] = [
            'process' => $proc,
            'stdin' => $pipes[0]
        ];

        $this->startRestream($key);
    }

    protected function stopFFmpeg(ConnectionInterface $conn)
    {
        $key = $conn->meta['key'] ?? null;
        if (!$key || !isset($this->ffmpeg[$key]))
            return;

        echo "🛑 Stopping FFmpeg local push: $key\n";

        fclose($this->ffmpeg[$key]['stdin']);
        proc_terminate($this->ffmpeg[$key]['process']);

        unset($this->ffmpeg[$key]);

        // Also stop restream
        $this->stopRestream($key);
    }

    protected function startRestream(string $key)
    {
        if (isset($this->restream[$key])) {
            echo "♻️ Restream already running for $key\n";
            return;
        }

        $input = "rtmp://localhost/live/$key";

        // Use ffprobe to check if RTMP has video
        $cmdProbe = "ffprobe -v error -show_entries stream=codec_type -of json '$input'";
        exec($cmdProbe, $out, $ret);
        if ($ret !== 0) {
            echo "⚠️ No active local RTMP, waiting for WebSocket push for $key\n";
            // We rely on WebSocket pushing video via startFFmpeg
            $input = "rtmp://localhost/live/$key"; // input will be available once FFmpeg starts
        }

        echo "🚀 Starting restream for $key\n";

        $cmd = [
            'ffmpeg',
            '-re',
            '-i',
            $input,
            '-c:v',
            'libx264',
            '-preset',
            'veryfast',
            '-tune',
            'zerolatency',
            '-c:a',
            'copy',
        ];

        foreach ($this->destinations as $dest) {
            $cmd[] = '-f';
            $cmd[] = 'flv';
            $cmd[] = $dest;
        }

        $desc = [
            0 => ['pipe', 'r'],
            1 => ['file', "/tmp/restream-$key.log", 'a'],
            2 => ['file', "/tmp/restream-$key.err", 'a']
        ];

        $proc = proc_open($cmd, $desc, $pipes);
        if (!is_resource($proc))
            return;

        $this->restream[$key] = [
            'process' => $proc,
            'pipes' => $pipes
        ];
    }

    protected function stopRestream(string $key)
    {
        if (!isset($this->restream[$key]))
            return;

        echo "🛑 Stopping restream for $key\n";
        proc_terminate($this->restream[$key]['process']);
        unset($this->restream[$key]);
    }


    protected function reapFFmpeg(string $key)
    {
        if (!isset($this->ffmpeg[$key]))
            return;

        $status = proc_get_status($this->ffmpeg[$key]['process']);
        if (!$status['running']) {
            fclose($this->ffmpeg[$key]['stdin']);
            unset($this->ffmpeg[$key]);
            echo "🧹 Reaped dead FFmpeg for $key\n";
        }
    }

    public function getActiveFFmpegKeys(): array
    {
        return array_keys($this->ffmpeg);
    }
    public function onClose(ConnectionInterface $conn)
    {
        $key = $conn->meta['key'] ?? null;
        if ($key) {
            $this->stopFFmpeg($conn);
        }
        $this->clients->detach($conn);
        echo "🔴 {$conn->resourceId} disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "💥 {$e->getMessage()}\n";
        $conn->close();
    }
}
?>