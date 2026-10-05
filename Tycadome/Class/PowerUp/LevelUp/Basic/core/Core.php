<?php
require_once __DIR__ . "/Womb/Tycadome.php";

require_once __DIR__ . "/../../../../../vendor/autoload.php";

use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class BasicServer extends TycadomeServer
{

    public function addSongsToArray($path, array &$array, int $index, $index2 = null, string $bucket = 'tsunami-radio')
    {

        try {
            $credentials = new Credentials($accessKey, $secretKey);
            $s3 = new S3Client([
                "region" => "auto",
                "endpoint" => $r2Endpoint,
                "version" => "latest",
                "credentials" => $credentials,
                "use_path_style_endpoint" => true
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            //respond(["error" => "Failed to initialize S3 client: " . $e->getMessage()]);
            exit;
        }
        if (!$s3) {
            error_log("addSongsToArray: Missing S3 client");
            return;
        }

        // Normalize prefix: remove leading slash, ensure trailing slash
        $prefix = ltrim($path, '/');
        if (substr($prefix, -1) !== '/')
            $prefix .= '/';

        try {
            $params = [
                "Bucket" => $bucket,
                "Prefix" => $prefix,
                "MaxKeys" => 1000
            ];

            $Objects = $s3->getPaginator('ListObjectsV2', $params);
            foreach ($Objects as $page) {
                if (!isset($page['Contents']))
                    continue;
                foreach ($page['Contents'] as $obj) {

                    // your mp3 handling logic
                    if (!isset($obj['Key']))
                        continue;
                    $key = $obj['Key'];

                    // only mp3
                    if (substr(strtolower($key), -4) !== '.mp3')
                        continue;

                    // Ensure index exists
                    if (!isset($array[$index]) || !is_array($array[$index])) {
                        $array[$index] = [];
                    }

                    $decodedKey = trim(urldecode(ltrim($key, '/')));

                    // If index2 provided, ensure the subarray exists
                    if ($index2 !== null) {
                        if (!isset($array[$index][$index2]) || !is_array($array[$index][$index2])) {
                            $array[$index][$index2] = [];
                        }
                        $array[$index][$index2][] = "https://radio.tsunamiflow.club/" . $decodedKey;
                    } else {
                        if ($index !== 11) {
                            $array[$index][] = "https://radio.tsunamiflow.club/" . $decodedKey;
                        }
                    }
                    if ($index !== 11) {
                        $array[11][] = "https://radio.tsunamiflow.club/" . $decodedKey;
                    }
                }
            }
        } catch (AwsException $e) {
            error_log("AWS Exception in paginator: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Exception in paginator: " . $e->getMessage());
        }
    }
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
            $this->addSongsToArray("Music/Rizz/IceBreakers/", $sentToJsArray, 0, 0);
            $this->addSongsToArray("Music/Rizz/Flirting/", $sentToJsArray, 0, 1);
            $this->addSongsToArray("Music/Rizz/GetHerDone/", $sentToJsArray, 0, 2);
            $this->addSongsToArray("Music/Rizz/Shots/", $sentToJsArray, 0, 3);

            // 1 a.m.
            $this->addSongsToArray("Music/Dance/Twerking/", $sentToJsArray, 1, 0);
            $this->addSongsToArray("Music/Dance/LineDance/", $sentToJsArray, 1, 1);
            $this->addSongsToArray("Music/Dance/PopDance/", $sentToJsArray, 1, 2);
            $this->addSongsToArray("Music/Dance/Battle/", $sentToJsArray, 1, 3);

            // 2 a.m
            $this->addSongsToArray("Music/Afterparty/", $sentToJsArray, 2, null);

            // 3 a.m Sex
            $this->addSongsToArray("Music/Sex/Foreplay/", $sentToJsArray, 3, 0);
            $this->addSongsToArray("Music/Sex/sex/", $sentToJsArray, 3, 1);
            $this->addSongsToArray("Music/Sex/Cuddle/", $sentToJsArray, 3, 2);

            // 4 a.m Love 
            $this->addSongsToArray("Music/Love/Memories/", $sentToJsArray, 4, 0);
            $this->addSongsToArray("Music/Love/love/", $sentToJsArray, 4, 1);
            $this->addSongsToArray("Music/Love/Intimacy/", $sentToJsArray, 4, 2);

            // 5 a.m Family
            $this->addSongsToArray("Music/Family/Lifestyle/", $sentToJsArray, 5, 0);
            $this->addSongsToArray("Music/Family/Values/", $sentToJsArray, 5, 1);
            $this->addSongsToArray("Music/Family/Kids/", $sentToJsArray, 5, 2);

            // 6 a.m Inspiration
            $this->addSongsToArray("Music/Inspiration/Motivation/", $sentToJsArray, 6, 0);
            $this->addSongsToArray("Music/Inspiration/Meditation/", $sentToJsArray, 6, 1);
            $this->addSongsToArray("Music/Inspiration/Something/", $sentToJsArray, 6, 2);

            // 7 a.m History 
            $this->addSongsToArray("Music/History/DH/", $sentToJsArray, 7, 0);
            $this->addSongsToArray("Music/History/BAH/", $sentToJsArray, 7, 1);
            $this->addSongsToArray("Music/History/HFnineteen/", $sentToJsArray, 7, 2);

            // 8 a.m Politics 
            $this->addSongsToArray("Music/Politics/Neutral/", $sentToJsArray, 8, 0);
            $this->addSongsToArray("Music/Politics/Democracy/", $sentToJsArray, 8, 1);
            $this->addSongsToArray("Music/Politics/Republican/", $sentToJsArray, 8, 2);
            $this->addSongsToArray("Music/Politics/Socialism/", $sentToJsArray, 8, 3);
            $this->addSongsToArray("Music/Politics/Bureaucracy/", $sentToJsArray, 8, 4);
            $this->addSongsToArray("Music/Politics/Aristocratic/", $sentToJsArray, 8, 5);

            // 9 a.m. Gaming 
            $this->addSongsToArray("Music/Gaming/Fighters/", $sentToJsArray, 9, 0);
            $this->addSongsToArray("Music/Gaming/Shooters/", $sentToJsArray, 9, 1);
            $this->addSongsToArray("Music/Gaming/Instrumentals/", $sentToJsArray, 9, 2);

            // 10 a.m. Comedy
            $this->addSongsToArray("Music/Comedy/", $sentToJsArray, 10, null);

            // 12 a.m Literature
            $this->addSongsToArray("Music/Literature/Poems/", $sentToJsArray, 12, 0);
            $this->addSongsToArray("Music/Literature/SS/", $sentToJsArray, 12, 1);
            $this->addSongsToArray("Music/Literature/Instrumentals/", $sentToJsArray, 12, 2);
            $this->addSongsToArray("Music/Literature/Books/", $sentToJsArray, 12, 3);

            // 1 p.m Sports 
            $this->addSongsToArray("Music/Sports/Reviews/", $sentToJsArray, 13, 0);
            $this->addSongsToArray("Music/Sports/Fans/", $sentToJsArray, 13, 1);
            $this->addSongsToArray("Music/Sports/Updates/", $sentToJsArray, 13, 2);
            $this->addSongsToArray("Music/Sports/Disses/", $sentToJsArray, 13, 3);

            // 2 p.m Tech
            $this->addSongsToArray("Music/Tech/News/", $sentToJsArray, 14, 0);
            $this->addSongsToArray("Music/Tech/Music/", $sentToJsArray, 14, 1);
            $this->addSongsToArray("Music/Tech/History/", $sentToJsArray, 14, 2);
            $this->addSongsToArray("Music/Tech/Instrumentals/", $sentToJsArray, 14, 3);

            // 3 p.m Science 
            $this->addSongsToArray("Music/Science/Biology/", $sentToJsArray, 15, 0);
            $this->addSongsToArray("Music/Science/Chemistry/", $sentToJsArray, 15, 1);
            $this->addSongsToArray("Music/Science/Physics/", $sentToJsArray, 15, 2);
            $this->addSongsToArray("Music/Science/Environmental/", $sentToJsArray, 15, 3);

            // 4 p.m Real Estate EP, Seller, Mortgage, Buyer
            $this->addSongsToArray("Music/RealEstate/EP/", $sentToJsArray, 16, 0);
            $this->addSongsToArray("Music/RealEstate/Seller/", $sentToJsArray, 16, 1);
            $this->addSongsToArray("Music/RealEstate/Mortgage/", $sentToJsArray, 16, 2);
            $this->addSongsToArray("Music/RealEstate/Buyer/", $sentToJsArray, 16, 3);

            // 5 p.m DJ Shuba (Basically New Music)
            $this->addSongsToArray("Music/DJshuba/", $sentToJsArray, 17, null);

            // 6 p.m Film
            $this->addSongsToArray("Music/Film/NM/", $sentToJsArray, 18, 0);
            $this->addSongsToArray("Music/Film/SHM/", $sentToJsArray, 18, 1);
            $this->addSongsToArray("Music/Film/MH/", $sentToJsArray, 18, 2);
            $this->addSongsToArray("Music/Film/VM/", $sentToJsArray, 18, 3);
            $this->addSongsToArray("Music/Film/LS/", $sentToJsArray, 18, 4);
            $this->addSongsToArray("Music/Film/CB/", $sentToJsArray, 18, 5);

            // 7 p.m Fashion PD, LD, FH, SM 
            $this->addSongsToArray("Music/Fashion/PD/", $sentToJsArray, 19, 0);
            $this->addSongsToArray("Music/Fashion/LD/", $sentToJsArray, 19, 1);
            $this->addSongsToArray("Music/Fashion/FH/", $sentToJsArray, 19, 2);
            $this->addSongsToArray("Music/Fashion/SM/", $sentToJsArray, 19, 3);

            // 8 p.m Business 
            $this->addSongsToArray("Music/Business/FE/", $sentToJsArray, 20, 0);
            $this->addSongsToArray("Music/Business/TOB/", $sentToJsArray, 20, 1);
            $this->addSongsToArray("Music/Business/Insurance/", $sentToJsArray, 20, 2);
            $this->addSongsToArray("Music/Business/TE/", $sentToJsArray, 20, 3);

            // Hustlin
            $this->addSongsToArray("Music/Hustlin/", $sentToJsArray, 21, null);

            // Pregame
            $this->addSongsToArray("Music/Pregame/", $sentToJsArray, 22, null);

            // Outside
            $this->addSongsToArray("Music/Outside/", $sentToJsArray, 23, null);


            // --- Finally output JSON ---
            $sentToJsArray[11] = array_values(array_unique($sentToJsArray[11]));
            $TsunamiFlowRadio = json_encode($sentToJsArray, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_SLASHES);
            echo TycadomeServer::respond($sentToJsArray);
            exit;
        }
    }

}
?>