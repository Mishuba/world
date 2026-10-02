<?php
require_once __DIR__ . "/LevelUp/Printul.php";

class TfMembers extends BeginnerStore
{
    public function CheckoutNewSignUp()
    {
        if (($xmljson['type'] ?? '') === 'Subscribers Signup') {
            $membership = $_POST['membershipLevel'] ?? 'free';
            $userData = $_POST;
            if (!empty($userData['TFRegisterPassword'])) {
                $userData['TFRegisterPassword'] = password_hash($userData['TFRegisterPassword'], PASSWORD_DEFAULT);
            }

            if ($membership === 'free') {
                InputIntoDatabase($membership, ...array_values($userData));
                respond(['success' => true, 'message' => 'Free membership created']);
            }

            $costMap = ['regular' => 400, 'vip' => 700, 'team' => 1000];
            $metadata = array_map('strval', $userData); // Convert all values to string for Stripe

            $s = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => $costMap[strtolower($membership)] ?? 2000,
                            'product_data' => ['name' => 'Community Member Signup Fee']
                        ],
                        'quantity' => 1
                    ]
                ],
                'success_url' => "$allowed_origins[2]/tfMain.php?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "$allowed_origins[2]/failed.php",
                'metadata' => $metadata
            ]);

            if (!empty($s->url)) {
                header("Location: " . $s->url);
                exit;
            } else {
                respond(['error' => 'Stripe session missing URL'], 500);
            }
        }
    }

public function InputIntoDatabase(
    $membership, $userName, $firstName, $lastName, $nickName, $gender, $birthdate, $email, $password,
    $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign, $vedicAstrologySign,
    $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign,
    $MayanZodiacSign, $loveLanguage, $birthStone, $birthFlower, $bloodType, $attachmentStyle, $charismaType, $businessPersonality,
    $TFuserDISC, $socionicsType, $learningStyle, $financialPersonalityType, $primaryMotivationStyle, $creativeStyle,
    $conflictManagementStyle, $teamRolePreference
){
    try {
        $db = TsunamiDatabaseFlow();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // --- Insert into FreeLevelMembers ---
        $stmt = $db->prepare("INSERT INTO Members (tfUN, tfFN, tfLN, tfNN, tfGen, tfBirth, tfEM, tfPSW, created)
            VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfGen, :tfBirth, :tfEM, :tfPSW, NOW())");
        $stmt->execute([
            ":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName,
            ":tfGen" => $gender, ":tfBirth" => $birthdate, ":tfEM" => $email, ":tfPSW" => $hashedPassword
        ]);

        // --- Session & Cookies ---
        foreach (["TfAccess" => ucfirst($membership), "Username" => $userName, "Birthday" => $birthdate,
                  "Gender" => $gender, "Nickname" => $nickName, "Email" => $email] as $k=>$v) {
//createCookieAndSession($k, $v);
                  } 

        // --- Additional inserts for Regular/VIP/Team members ---
        $tableMap = [
            "Regular" => "RegularMembers",
            "VIP" => "VIPMembers",
            "Team" => "TeamMembers"
        ];

        if (isset($tableMap[$membership])) {
            $stmtExtra = $db->prepare("INSERT INTO {$tableMap[$membership]} 
                (tfUN, tfFN, tfLN, tfNN, tfEM, tfBirth, tfGen, created) 
                VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfEM, :tfBirth, :tfGen, NOW())");
            $stmtExtra->execute([
                ":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName,
                ":tfEM" => $email, ":tfBirth" => $birthdate, ":tfGen" => $gender
            ]);
        }

        // --- CSV Backup ---
        $csvFile = __DIR__ . "/user_backup.csv";
        $csvData = [
            $userName, $firstName, $lastName, $nickName, $gender, $birthdate, $email,
            $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign,
            $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype,
            $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $loveLanguage, $birthStone,
            $birthFlower, $bloodType, $attachmentStyle, $charismaType, $businessPersonality, $TFuserDISC,
            $socionicsType, $learningStyle, $financialPersonalityType, $primaryMotivationStyle, $creativeStyle,
            $conflictManagementStyle, $teamRolePreference, date("Y-m-d H:i:s")
        ];
        $handle = fopen($csvFile, 'a');
        fputcsv($handle, $csvData);
        fclose($handle);

        echo json_encode(["status"=>"success","message"=>"User $userName successfully registered as $membership member."]);

    } catch (PDOException $e) {
        handleDatabaseError($e);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        echo json_encode(["status"=>"error","message"=>"Unexpected error: ".$e->getMessage()]);
    }
}
}