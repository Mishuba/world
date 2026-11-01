<?php
require_once __DIR__ . "/Webhook/Social.php";

class Ruby extends TsunamiWebhooks
{

    public function RubyEmailer()
    {
        if (!$this->xmljson) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON body"]);
            exit;
        }

        /*
        foreach ($this->UiPathTypes as $field) {
            if (!isset($this->xmljson[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing field: $field"]);
                exit;
            }
        }
        */
        function cleanInput($input, $max = 5000, $isUrl = false)
        {
            $value = substr(trim($input), 0, $max);
            if ($isUrl) {
                return filter_var($value, FILTER_VALIDATE_URL) ?: null;
            }
            return $value;
        }
        $decisionOption = cleanInput($this->xmljson['WhatWeDoinBro'], 200);
        $msgSub = cleanInput($this->xmljson['MessageSubject'], 200);
        $msgStart = cleanInput($this->xmljson['MessageStart'], 200);
        $msgContinue = cleanInput($this->xmljson['MessageContinue'], 200);

        $ytLink = cleanInput($this->xmljson['YoutubeLink'], 200, true);
        $spotLink = cleanInput($this->xmljson['SpotifyLink'], 200, true);
        $appleLink = cleanInput($this->xmljson['AppleLink'], 200, true);
        $wavDl = cleanInput($this->xmljson['WavDownloadLink'], 200, true);
        $mp3Dl = cleanInput($this->xmljson['Mp3DownloadLink'], 200, true);

        foreach (['ytLink', 'spotLink', 'appleLink', 'wavDl', 'mp3Dl'] as $var) {
            if ($$var === null) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid URL for $var"]);
                exit;
            }
        }

        try {
            $inputArguments = json_encode([
                "songSubject" => $msgSub,
                "startMessage" => $msgStart,
                "mp3" => $mp3Dl,
                "wav" => $wavDl,
                "youtube" => $ytLink,
                "spotify" => $spotLink,
                "itunes" => $appleLink,
                "continueMessage" => $msgContinue,
                "apiReason" => $decisionOption
            ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(["error" => "InputArguments encoding failed"]);
            exit;
        }

        $accessToken = getenv("UiPath_Access_Token");

        $triggerUrl = getenv("UiPath_TRIGGER_URL");

        $triggerPayload = json_encode([
            "songSubject" => $msgSub,
            "startMessage" => $msgStart,
            "mp3" => $mp3Dl,
            "wav" => $wavDl,
            "youtube" => $ytLink,
            "spotify" => $spotLink,
            "itunes" => $appleLink,
            "continueMessage" => $msgContinue,
            "apiReason" => $decisionOption
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $ch = curl_init($triggerUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $triggerPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer $accessToken"
            ]
        ]);

        $triggerResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($triggerResponse === false) {
            http_response_code(500);
            echo json_encode([
                "error" => "cURL transport failure",
                "curl_error" => $curlErr
            ]);
            exit;
        }

        if ($httpCode !== 200) {
            http_response_code(500);
            echo json_encode([
                "error" => "Trigger failed",
                "status" => $httpCode,
                "uipath_raw" => $triggerResponse
            ]);
            exit;
        }

        echo json_encode([
            "status" => "triggered",
            "uipath_raw" => json_decode($triggerResponse, true)
        ]);
        exit;
    }
}
?>