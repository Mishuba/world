<?php
session_start();
require_once 'vendor/autoload.php'; 
// webhook.php

// === CONFIGURATION ===
$keys = [
    'twitch' => $_POST['twitch_key'] ?? '',
    'youtube' => $_POST['youtube_key'] ?? '',
    'facebook' => $_POST['facebook_key'] ?? '',
    'instagram' => $_POST['instagram_key'] ?? '',
];

$config = [
    'youtube_token' => getenv("YOUTUBE_ACCESS_TOKEN"),
    'twitch_token' => getenv("TWITCH_ACCESS_TOKEN"),
    'facebook_token' => getenv("FACEBOOK_ACCESS_TOKEN"),
    'facebook_page_id' => getenv("FACEBOOK_PAGE_ID"),
    'instagram_token' => getenv("INSTAGRAM_ACCESS_TOKEN"),
    'instagram_page_id' => getenv("INSTAGRAM_PAGE_ID"),
    'tumblr_token' => getenv("TUMBLR_ACCESS_TOKEN"),
    'pinterest_token' => getenv("PINTEREST_ACCESS_TOKEN"),
    'twitter_token' => getenv("TWITTER_BEARER_TOKEN"),
    'allowed_ips' => ['127.0.0.1'], // Add your IP here for security
];

$data = [
    'title' => $title,
    'description' => $description,
    'platforms' => $platforms,
    'keys' => $keys,
    'timestamp' => time()
];

//file_put_contents("streaminfo.json", json_encode($data));

// Optional future DB insertion (disabled for now)
/*
$pdo = new PDO("mysql:host=localhost;dbname=yourdb", "user", "pass");
$stmt = $pdo->prepare("INSERT INTO streams (title, description, time) VALUES (?, ?, NOW())");
$stmt->execute([$title, $description]);
*/

//// Build exec command  $cmd = "/usr/local/bin/ffmpeg-restream.sh";  exec("nohup $cmd > /dev/null 2>&1 &");  echo json_encode(["success" => true]);

// === BASIC ACCESS CONTROL ===
if (!in_array($_SERVER['REMOTE_ADDR'], $config['allowed_ips'])) {
    http_response_code(403);
    exit("Access Denied");
}
// === UI ===

//////////////////////// API Update Functions ////////////////////////
function updateYouTube($title, $description) {
    global $config;
    $broadcastId = "YOUR_BROADCAST_ID";

    $url = "https://www.googleapis.com/youtube/v3/liveBroadcasts?part=snippet";
    $urlVideo = "https://www.googleapis.com/youtube/v3/videos?part=snippet";
    $data = [
        'id' => $broadcastId,
        'snippet' => [
            'title' => $title,
            'description' => $description
        ]
    ];

    $headers = [
    "Authorization: Bearer {$config['youtube_token']}",
    "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("YouTube update failed: $response");
    }
    curl_close($ch);
}

function updateTwitch($title) {
    global $config;
    $clientId = "YOUR_TWITCH_CLIENT_ID";
    $channelId = "YOUR_CHANNEL_ID";

    $url = "https://api.twitch.tv/helix/channels?broadcaster_id=$channelId";
    $data = ['title' => $title];
    $headers = [
        "Authorization: Bearer {$config['twitch_token']}",
        "Client-Id: $clientId",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 204) {
        error_log("Twitch update failed: $response");
    }
    curl_close($ch);
}

function updateTwitter($title, $description) {
    global $config;
    // Simplified example — Twitter v2 preferred now
    $url = "https://api.twitter.com/2/tweets";
        $data = ["text" => "$title\n\n$description"];
        $headers = [
            "Authorization: Bearer {$config['twitter_token']}",
            "Content-Type: application/json"
        ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Twitter update failed: $response");
    }
    curl_close($ch);
}

function updateInstagram($title, $description) {
    // Facebook Graph API for Instagram posting
    $accessToken = "YOUR_INSTAGRAM_ACCESS_TOKEN";
    $pageId = "YOUR_INSTAGRAM_PAGE_ID";

    $url = "https://graph.facebook.com/v12.0/$pageId/media";
    $data = [
        'caption' => "$title\n\n$description",
        'access_token' => $accessToken,
        'image_url' => 'https://your-image-url.com/image.jpg' // Instagram requires a media URL!
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("Instagram update failed: $response");
    }
    curl_close($ch);
}

function updateFacebook($title, $description) {
    global $config;

    $accessToken = "YOUR_FACEBOOK_ACCESS_TOKEN";
    $liveVideoId = "YOUR_LIVE_VIDEO_ID";
    $headers = ["Authorization: Bearer {$config['facebook_token']}","Content-Type: application/json"];
                    $data = ["message" => "$title\n\n$description"];

    $url = "https://graph.facebook.com/v12.0/$liveVideoId";
    $urlchat = "https://graph.facebook.com/{$config['facebook_page_id']}/feed";
    $data = [
        'title' => $title,
        'description' => $description,
        'access_token' => $accessToken
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("Facebook update failed: $response");
    }
    curl_close($ch);
}

function updateTikTok($title, $description) {
    // Placeholder: TikTok API needs a proper App setup
    error_log("TikTok API update function not implemented yet.");
}

function updateTumblr($title, $description) {
    global $config;
    $accessToken = "YOUR_TUMBLR_ACCESS_TOKEN";
    $blogName = "YOUR_BLOG_NAME.tumblr.com";

    $url = "https://api.tumblr.com/v2/blog/$blogName/post";
    $data = [
        'type' => 'text',
        'title' => $title,
        'body' => $description,
        'access_token' => $accessToken
    ];
    $headers = ["Authorization: Bearer {$config['tumblr_token']}", "Content-Type: application/json"];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Tumblr update failed: $response");
    }
    curl_close($ch);
}

function updatePinterest($title, $description) {
    global $config;

    $url = "https://api.pinterest.com/v5/pins";
    $headers = [
        "Authorization: Bearer {$config['pinterest_token']}",
        "Content-Type: application/json"
    ];
    $data = [
        "title" => $title,
        "description" => $description,
        "link" => "https://yourdomain.com",
        "board_id" => "YOUR_BOARD_ID",
        "media_source" => ["source_type" => "image_url", "url" => "https://yourdomain.com/image.jpg"]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Pinterest update failed: $response");
    }
    curl_close($ch);
}

// === HANDLE POST REQUESTS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        if (isset($_POST['streamUrl'])) {
            // === RTMP START ===
            $streamUrl = escapeshellarg($_POST['streamUrl']);
            $pidFile = "/tmp/ffmpeg_pid_" . md5($streamUrl) . ".pid";
            $cmd = "ffmpeg -re -i rtmp://localhost/live/stream -c copy -f flv $streamUrl > /dev/null 2>&1 & echo $!";

            $output = shell_exec($cmd);
            if ($output) {
                file_put_contents($pidFile, trim($output));
            }

        } elseif (isset($_POST['stopStreamUrl'])) {
            // === RTMP STOP ===
            $streamUrl = escapeshellarg($_POST['stopStreamUrl']);
            $pidFile = "/tmp/ffmpeg_pid_" . md5($streamUrl) . ".pid";

            if (file_exists($pidFile)) {
                $pid = trim(file_get_contents($pidFile));
                shell_exec("kill $pid");
                unlink($pidFile);
            }

        } else if (isset($_POST['platforms'])) {
        $input = json_decode(file_get_contents('php://input'), true);

        $title = $input['title'] ?? '';
        $description = $input['description'] ?? '';
        $platforms = $input['platforms'] ?? [];

        foreach ($platforms as $platform) {
            switch ($platform) {
                case 'youtube':
                    updateYouTube($title, $description);
                    break;
                case 'twitch':
                    updateTwitch($title);
                    break;
                case 'x':
                    updateTwitter($title, $description);
                    break;
                case 'instagram':
                    updateInstagram($title, $description);
                    break;
                case 'facebook':
                    updateFacebook($title, $description);
                    break;
                case 'tiktok':
                    updateTikTok($title, $description);
                    break;
                case 'tumblr':
                    updateTumblr($title, $description);
                    break;
                case 'pinterest':
                    updatePinterest($title, $description);
                    break;
                default:
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    $response = curl_exec($ch);
                    curl_close($ch);
                    echo "Response: $response";
                    exit("Unknown platform");
            }
        }
        exit('Metadata updated.');
        }
    }

    if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
        // Start new RTMP stream
        if (!isset($_SESSION['processes'])) {
            $_SESSION['processes'] = [];
        }

        $rtmp_input = "rtmp://world.tsunamiflow.club/live/anything";
        if (isset($_POST['new_destination'])) {
            $new_destination = escapeshellarg($_POST['new_destination']);
            $cmd = "ffmpeg -i $rtmp_input -c:v copy -c:a copy -f flv $new_destination > /dev/null 2>&1 & echo $!";
            exec($cmd, $output);

            $_SESSION['processes'][] = ['cmd' => $cmd, 'pid' => $output[0]];

            echo "Stream added to: $new_destination";
            exit;
        }
    }
}
?>
