<?php
// --- ERRORS ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


// --- Required files & namespaces ---
//require_once "Arrays.php";
require_once __DIR__ . "/../Variables/tycadomeVariables.php";
require_once __DIR__ . "/../../vendor/autoload.php";



/* ---------- CSV WRITER ---------- */
function appendToCSV(string $file, array $values, array $header = [])
{
    $exists = file_exists($file);
    $f = fopen($file, $exists ? "a" : "w");

    if (!$exists && $header) {
        fputcsv($f, $header);
    }

    fputcsv($f, $values);
    fclose($f);
}

//Email
function sendReceipt($to, $orderDetails)
{
    /*
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.yourserver.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your@email.com';
        $mail->Password = 'yourpassword';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your@email.com', 'Tsunami Flow Store');
        $mail->addAddress($to);
        $mail->Subject = 'Your Receipt';
        $mail->Body = "Thank you for your order!\n\n" . print_r($orderDetails, true);
        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: ".$e->getMessage());
    }
        */
}


//Webrtc Functions 
//curl response to make php 
/*
curl -X POST \
    -H "Authorization: Bearer $APITOKEN" \
    -H "Content-TYpe: application/json" -d `{"ttl": 86400}` \
    https://rtc.live.cloudlflare.com/v1/turn/keys/$TurnTokenID/credentials/generate 

    JSON response (Passed to Servers) 
    {
        "iceServers": {
            "urls": [
                "stun:stun.cloudflare.com:3478",
                "turn:turn.cloudflare.com:3478?transport=udp",
                "turn:turn.cloudflare.com:3478?transport=tcp",
                "turn:turn.cloudflare.com:3478?transport=tcp"
                ],
                "username": "mishuba14@gmail.com" //i think,
                "credential": "#SuperSayian14+" //i think, 
                }
    }
*/

function updateYouTube($title, $description)
{
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("YouTube update failed: $response");
    }
    curl_close($ch);
}

function updateTwitch($title)
{
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 204) {
        error_log("Twitch update failed: $response");
    }
    curl_close($ch);
}

function updateTwitter($title, $description)
{
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Twitter update failed: $response");
    }
    curl_close($ch);
}

function updateInstagram($title, $description)
{
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("Instagram update failed: $response");
    }
    curl_close($ch);
}

function updateFacebook($title, $description)
{
    global $config;

    $accessToken = "YOUR_FACEBOOK_ACCESS_TOKEN";
    $liveVideoId = "YOUR_LIVE_VIDEO_ID";
    $headers = ["Authorization: Bearer {$config['facebook_token']}", "Content-Type: application/json"];
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("Facebook update failed: $response");
    }
    curl_close($ch);
}

function updateTikTok($title, $description)
{
    // Placeholder: TikTok API needs a proper App setup
    error_log("TikTok API update function not implemented yet.");
}

function updateTumblr($title, $description)
{
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Tumblr update failed: $response");
    }
    curl_close($ch);
}

function updatePinterest($title, $description)
{
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
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Pinterest update failed: $response");
    }
    curl_close($ch);
}
?>