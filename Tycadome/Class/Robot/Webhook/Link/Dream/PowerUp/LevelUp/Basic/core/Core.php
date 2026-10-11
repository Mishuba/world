<?php
require_once __DIR__ . "/Womb/Tycadome.php";

class BasicServer extends TycadomeServer
{

    public $sentToJsArray = array();
    public function __construct()
    {
        $this->sentToJsArray = array(
            array(array(), array(), array(), array()), // 0 Rizz
            array(array(), array(), array(), array()), // 1 Dancing
            array(), // 2 After Club
            array(array(), array(), array()), // 3 Sex
            array(array(), array(), array()), // 4 Love
            array(array(), array(), array()), // 5 Family
            array(array(), array(), array()), // 6 Inspiration
            array(array(), array(), array()), // 7 History
            array(array(), array(), array(), array(), array(), array()), // 8 Politics
            array(array(), array(), array()), // 9 Gaming
            array(), // 10 Comedy
            array(), // 11 All Music (Everything)
            array(array(), array(), array(), array()), // 12 Literature
            array(array(), array(), array(), array()), // 13 Sports
            array(array(), array(), array(), array()), // 14 Tech
            array(array(), array(), array(), array()), // 15 Science
            array(array(), array(), array(), array()), // 16 Real Estate
            array(), // 17 DJshuba
            array(array(), array(), array(), array(), array(), array()), // 18 Film
            array(array(), array(), array(), array()), // 19 Fashion
            array(array(), array(), array(), array()), // 20 Business
            array(), // 21 Hustlin
            array(), // 22 Pregame
            array()  // 23 Outside
        );
    }
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
    public function RadioPlaylist($reType, string $accessKey, string $secretKey, string $r2Endpoint)
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
            $this->addSongsToArray("Music/Rizz/IceBreakers/", $this->sentToJsArray, 0, 0);
            $this->addSongsToArray("Music/Rizz/Flirting/", $this->sentToJsArray, 0, 1);
            $this->addSongsToArray("Music/Rizz/GetHerDone/", $this->sentToJsArray, 0, 2);
            $this->addSongsToArray("Music/Rizz/Shots/", $this->sentToJsArray, 0, 3);

            // 1 a.m.
            $this->addSongsToArray("Music/Dance/Twerking/", $this->sentToJsArray, 1, 0);
            $this->addSongsToArray("Music/Dance/LineDance/", $this->sentToJsArray, 1, 1);
            $this->addSongsToArray("Music/Dance/PopDance/", $this->sentToJsArray, 1, 2);
            $this->addSongsToArray("Music/Dance/Battle/", $this->sentToJsArray, 1, 3);

            // 2 a.m
            $this->addSongsToArray("Music/Afterparty/", $this->sentToJsArray, 2, null);

            // 3 a.m Sex
            $this->addSongsToArray("Music/Sex/Foreplay/", $this->sentToJsArray, 3, 0);
            $this->addSongsToArray("Music/Sex/sex/", $this->sentToJsArray, 3, 1);
            $this->addSongsToArray("Music/Sex/Cuddle/", $this->sentToJsArray, 3, 2);

            // 4 a.m Love 
            $this->addSongsToArray("Music/Love/Memories/", $this->sentToJsArray, 4, 0);
            $this->addSongsToArray("Music/Love/love/", $this->sentToJsArray, 4, 1);
            $this->addSongsToArray("Music/Love/Intimacy/", $this->sentToJsArray, 4, 2);

            // 5 a.m Family
            $this->addSongsToArray("Music/Family/Lifestyle/", $this->sentToJsArray, 5, 0);
            $this->addSongsToArray("Music/Family/Values/", $this->sentToJsArray, 5, 1);
            $this->addSongsToArray("Music/Family/Kids/", $this->sentToJsArray, 5, 2);

            // 6 a.m Inspiration
            $this->addSongsToArray("Music/Inspiration/Motivation/", $this->sentToJsArray, 6, 0);
            $this->addSongsToArray("Music/Inspiration/Meditation/", $this->sentToJsArray, 6, 1);
            $this->addSongsToArray("Music/Inspiration/Something/", $this->sentToJsArray, 6, 2);

            // 7 a.m History 
            $this->addSongsToArray("Music/History/DH/", $this->sentToJsArray, 7, 0);
            $this->addSongsToArray("Music/History/BAH/", $this->sentToJsArray, 7, 1);
            $this->addSongsToArray("Music/History/HFnineteen/", $this->sentToJsArray, 7, 2);

            // 8 a.m Politics 
            $this->addSongsToArray("Music/Politics/Neutral/", $this->sentToJsArray, 8, 0);
            $this->addSongsToArray("Music/Politics/Democracy/", $this->sentToJsArray, 8, 1);
            $this->addSongsToArray("Music/Politics/Republican/", $this->sentToJsArray, 8, 2);
            $this->addSongsToArray("Music/Politics/Socialism/", $this->sentToJsArray, 8, 3);
            $this->addSongsToArray("Music/Politics/Bureaucracy/", $this->sentToJsArray, 8, 4);
            $this->addSongsToArray("Music/Politics/Aristocratic/", $this->sentToJsArray, 8, 5);

            // 9 a.m. Gaming 
            $this->addSongsToArray("Music/Gaming/Fighters/", $this->sentToJsArray, 9, 0);
            $this->addSongsToArray("Music/Gaming/Shooters/", $this->sentToJsArray, 9, 1);
            $this->addSongsToArray("Music/Gaming/Instrumentals/", $this->sentToJsArray, 9, 2);

            // 10 a.m. Comedy
            $this->addSongsToArray("Music/Comedy/", $this->sentToJsArray, 10, null);

            // 12 a.m Literature
            $this->addSongsToArray("Music/Literature/Poems/", $this->sentToJsArray, 12, 0);
            $this->addSongsToArray("Music/Literature/SS/", $this->sentToJsArray, 12, 1);
            $this->addSongsToArray("Music/Literature/Instrumentals/", $this->sentToJsArray, 12, 2);
            $this->addSongsToArray("Music/Literature/Books/", $this->sentToJsArray, 12, 3);

            // 1 p.m Sports 
            $this->addSongsToArray("Music/Sports/Reviews/", $this->sentToJsArray, 13, 0);
            $this->addSongsToArray("Music/Sports/Fans/", $this->sentToJsArray, 13, 1);
            $this->addSongsToArray("Music/Sports/Updates/", $this->sentToJsArray, 13, 2);
            $this->addSongsToArray("Music/Sports/Disses/", $this->sentToJsArray, 13, 3);

            // 2 p.m Tech
            $this->addSongsToArray("Music/Tech/News/", $this->sentToJsArray, 14, 0);
            $this->addSongsToArray("Music/Tech/Music/", $this->sentToJsArray, 14, 1);
            $this->addSongsToArray("Music/Tech/History/", $this->sentToJsArray, 14, 2);
            $this->addSongsToArray("Music/Tech/Instrumentals/", $this->sentToJsArray, 14, 3);

            // 3 p.m Science 
            $this->addSongsToArray("Music/Science/Biology/", $this->sentToJsArray, 15, 0);
            $this->addSongsToArray("Music/Science/Chemistry/", $this->sentToJsArray, 15, 1);
            $this->addSongsToArray("Music/Science/Physics/", $this->sentToJsArray, 15, 2);
            $this->addSongsToArray("Music/Science/Environmental/", $this->sentToJsArray, 15, 3);

            // 4 p.m Real Estate EP, Seller, Mortgage, Buyer
            $this->addSongsToArray("Music/RealEstate/EP/", $this->sentToJsArray, 16, 0);
            $this->addSongsToArray("Music/RealEstate/Seller/", $this->sentToJsArray, 16, 1);
            $this->addSongsToArray("Music/RealEstate/Mortgage/", $this->sentToJsArray, 16, 2);
            $this->addSongsToArray("Music/RealEstate/Buyer/", $this->sentToJsArray, 16, 3);

            // 5 p.m DJ Shuba (Basically New Music)
            $this->addSongsToArray("Music/DJshuba/", $this->sentToJsArray, 17, null);

            // 6 p.m Film
            $this->addSongsToArray("Music/Film/NM/", $this->sentToJsArray, 18, 0);
            $this->addSongsToArray("Music/Film/SHM/", $this->sentToJsArray, 18, 1);
            $this->addSongsToArray("Music/Film/MH/", $this->sentToJsArray, 18, 2);
            $this->addSongsToArray("Music/Film/VM/", $this->sentToJsArray, 18, 3);
            $this->addSongsToArray("Music/Film/LS/", $this->sentToJsArray, 18, 4);
            $this->addSongsToArray("Music/Film/CB/", $this->sentToJsArray, 18, 5);

            // 7 p.m Fashion PD, LD, FH, SM 
            $this->addSongsToArray("Music/Fashion/PD/", $this->sentToJsArray, 19, 0);
            $this->addSongsToArray("Music/Fashion/LD/", $this->sentToJsArray, 19, 1);
            $this->addSongsToArray("Music/Fashion/FH/", $this->sentToJsArray, 19, 2);
            $this->addSongsToArray("Music/Fashion/SM/", $this->sentToJsArray, 19, 3);

            // 8 p.m Business 
            $this->addSongsToArray("Music/Business/FE/", $this->sentToJsArray, 20, 0);
            $this->addSongsToArray("Music/Business/TOB/", $this->sentToJsArray, 20, 1);
            $this->addSongsToArray("Music/Business/Insurance/", $this->sentToJsArray, 20, 2);
            $this->addSongsToArray("Music/Business/TE/", $this->sentToJsArray, 20, 3);

            // Hustlin
            $this->addSongsToArray("Music/Hustlin/", $this->sentToJsArray, 21, null);

            // Pregame
            $this->addSongsToArray("Music/Pregame/", $this->sentToJsArray, 22, null);

            // Outside
            $this->addSongsToArray("Music/Outside/", $this->sentToJsArray, 23, null);


            // --- Finally output JSON ---
            $this->sentToJsArray[11] = array_values(array_unique($this->sentToJsArray[11]));
            $TsunamiFlowRadio = json_encode($this->sentToJsArray, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_SLASHES);
            echo TycadomeServer::respond($this->sentToJsArray);
            exit;
        }
    }

}
?>