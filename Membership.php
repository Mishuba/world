<?php

function db() {
    return TsunamiDatabaseFlow();
}

/* ---------- GENERIC HELPERS ---------- */

function writeCookies(array $data, int $days = 30) {
    $expiry = time() + (86400 * $days);
    foreach ($data as $key => $value) {
        setcookie($key, $value, $expiry);
    }
}

function writeSession(array $data) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    foreach ($data as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

function insertRow(string $table, array $columns) {
    $keys = array_keys($columns);
    $placeholders = array_map(fn($k) => ":$k", $keys);

    $sql = "INSERT INTO {$table} (" . implode(",", $keys) . ")
            VALUES (" . implode(",", $placeholders) . ")";

    $pdo = db();
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_combine($placeholders, array_values($columns)));
}

/* ---------- CSV WRITER ---------- */
function appendToCSV(string $file, array $values, array $header = []) {
    $exists = file_exists($file);
    $f = fopen($file, $exists ? "a" : "w");

    if (!$exists && $header) {
        fputcsv($f, $header);
    }

    fputcsv($f, $values);
    fclose($f);
}

/* ---------- MAIN FUNCTION ---------- */

function InputIntoDatabase(
    $membership, $userName, $firstName, $lastName, $nickName, $gender,
    $birthdate, $email, $password, $chineseZodiacSign, $westernZodiacSign,
    $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign,
    $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning,
    $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign,
    $MayanZodiacSign, $loveLanguage, $birthStone, $birthFlower, $bloodType,
    $attachmentStyle, $charismaType, $businessPersonality, $TFuserDISC,
    $socionicsType, $learningStyle, $financialPersonalityType,
    $primaryMotivationStyle, $creativeStyle, $conflictManagementStyle,
    $teamRolePreference
) {

    // SECURITY FIX — HASH PASSWORD
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $baseData = [
        "tfUN"    => $userName,
        "tfFN"    => $firstName,
        "tfLN"    => $lastName,
        "tfNN"    => $nickName,
        "tfGen"   => $gender,
        "tfBirth" => $birthdate,
        "tfEM"    => $email,
        "tfPSW"   => $hashed,
        "created" => date("Y-m-d H:i:s")
    ];

    try {

        /* ===== FREE MEMBERSHIP ===== */
        if ($membership === "free") {

            insertRow("FreeLevelMembers", $baseData);

            writeCookies([
                "TfAccess" => "Free",
                "Username" => $userName,
                "Birthday" => $birthdate,
                "Gender"   => $gender,
                "Nickname" => $nickName,
                "Email"    => $email
            ]);

            writeSession([
                "TfAccess" => "Free",
                "Username" => $userName,
                "Birthday" => $birthdate,
                "Gender"   => $gender,
                "Nickname" => $nickName,
                "Email"    => $email
            ]);

            appendToCSV(
                "./TDFB/CSV/Members/free.csv",
                [$userName, $birthdate, $gender, $nickName, $email],
                ["Username", "Birthday", "Gender", "Nickname", "Email"]
            );

            echo "welcome $userName you are now a free member";
        }

        /* ===== REGULAR MEMBERSHIP ===== */
        if ($membership === "regular") {

            insertRow("FreeLevelMembers", $baseData);

            insertRow("RegularMembers", [
                "tfUN" => $userName,
                "ChineseZodiacSign"         => $chineseZodiacSign,
                "WesternZodiacSign"         => $westernZodiacSign,
                "SpiritAnimal"              => $spiritAnimal,
                "CelticTreeZodiacSign"      => $celticTreeZodiacSign,
                "NativeAmericanZodiacSign"  => $nativeAmericanZodiacSign,
                "VedicAstrologySign"        => $vedicAstrologySign,
                "GuardianAngel"             => $guardianAngel,
                "ChineseElement"            => $ChineseElement,
                "EyeColorMeaning"           => $eyeColorMeaning,
                "GreekMythologyArchetype"   => $GreekMythologyArchetype,
                "NorseMythologyPatronDeity" => $NorseMythologyPatronDeity,
                "EgyptianZodiacSign"        => $EgyptianZodiacSign,
                "MayanZodiacSign"           => $MayanZodiacSign
            ]);

            writeCookies([
                "TfAccess" => "Regular",
                "Username" => $userName,
                "Birthday" => $birthdate,
                "Gender"   => $gender,
                "Nickname" => $nickName,
                "Email"    => $email,
                "ChineseZodiacSign" => $chineseZodiacSign,
                "WesternZodiacSign" => $westernZodiacSign,
                "SpiritAnimal" => $spiritAnimal
                // Add more cookies only if truly needed
            ]);

            writeSession([
                "TfAccess" => "Regular",
                "Username" => $userName,
                "Birthday" => $birthdate,
                "Gender"   => $gender,
                "Nickname" => $nickName,
                "Email"    => $email,
                "ChineseZodiacSign" => $chineseZodiacSign
                // Add more session values if needed
            ]);

            echo "welcome $userName you are now a regular member";
        }

    } catch (PDOException $e) {
        handleDatabaseError($e);
    }
}