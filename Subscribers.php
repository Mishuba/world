<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
 
function InputIntoDatabase($membership, $userName, $firstName, $lastName, $nickName, $gender, $birthdate, $email, $password, $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign, $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $loveLanguage, $birthStone, $birthFlower, $bloodType, $attachmentStyle, $charismaType, $businessPersonality, $TFuserDISC, $socionicsType, $learningStyle, $financialPersonalityType, $primaryMotivationStyle, $creativeStyle, $conflictManagementStyle, $teamRolePreference){
    switch ($membership) {
        case "free":
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $SomeCommunityShitIguess = $TycadomeDatabase->prepare("INSERT INTO FreeLevelMembers (tfUN, tfFN, tfLN, tfNN, tfGen, tfBirth, tfEM, tfPSW, created) VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfGen, :tfBirth, :tfEM, :tfPSW, NOW())");

                $SomeCommunityShitIguess->execute([":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName, ":tfGen" => $gender, ":tfBirth" => $birthdate, ":tfEM" => $email, ":tfPSW" => $password]);

                //create cookies
                setcookie("TfAccess", "Free",time() + (86400 * 30));
                setcookie("Username", $userName, time() + (86400 * 30));
                setcookie("Birthday", $birthdate, time() + (86400 * 365000));
                setcookie("Gender", $gender, time() + (86400 * 365000));
                setcookie("Nickname", $nickName, time() + (86400 * 365000));
                setcookie("Email", $email, time() + (86400 * 365));                

                //create Session Variables
                $_SESSION["TfAccess"] = "Free";
                $_SESSION["Username"] = $userName;
                $_SESSION["Birthday"] = $birthdate;
                $_SESSION["Gender"] = $gender;
                $_SESSION["Nickname"] = $nickName;
                $_SESSION["Email"] = $email;
                session_start();
                session_regenerate_id(true);

                //csv database version. Simple like a cookie
                $TheEntireFormFr = [$userName, $birthdate, $gender, $nickName, $email];
                $CSVfile = fopen("./TDFB/CSV/Members/free.csv", "a");

                    if($CSVfile) {
                        if($CSVfile)
                        fputcsv($CSVfile, $TheEntireFormFr);
                        fclose($CSVfile);
                    } else {
                        $newCSVfile = fopen("./TDFB/CSV/Members/free.csv", "w");

                        if ($newCSVfile) {
                            fputcsv($newCSVfile, ["Username", "Birthday", "Gender"]);
                            fputcsv($newCSVfile, $TheEntireFormFr);
                            fclose($newCSVfile);
                        }
                    }
                echo ("welcome $userName you are now a member");
            } catch (PDOException $SomeErrorFr) {
                handleDatabaseError($SomeErrorFr);
            } finally {
                $SomeCommunityShitIguess = null;
                $TycadomeDatabase = null;
            }
            break;
        case "regular":
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $SomeCommunityShitIguess = $TycadomeDatabase->prepare("INSERT INTO FreeLevelMembers (tfUN, tfFN, tfLN, tfNN, tfGen, tfBirth, tfEM, tfPSW, created) VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfGen, :tfBirth, :tfEM, :tfPSW, NOW())");

                $SomeCommunityShitIguess->execute([":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName, ":tfGen" => $gender, ":tfBirth" => $birthdate, ":tfEM" => $email, ":tfPSW" => $password]);
            } catch (PDOException $SomeErrorFr) {
                handleDatabaseError($SomeErrorFr);
            } finally {
                $SomeCommunityShitIguess = null;
                $TycadomeDatabase = null;
            }
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $RegularPeopleShit = $TycadomeDatabase->prepare("INSERT INTO RegularMembers (tfUN, ChineseZodiacSign, WesternZodiacSign, SpiritAnimal, CelticTreeZodiacSign, NativeAmericanZodiacSign, VedicAstrologySign, GuardianAngel, ChineseElement, EyeColorMeaning, GreekMythologyArchetype, NorseMythologyPatronDeity, EgyptianZodiacSign, MayanZodiacSign) VALUES (:tfUN, :ChineseZodiacSign, :WesternZodiacSign, :SpiritAnimal, :CelticTreeZodiacSign, :NativeAmericanZodiacSign, :VedicAstrologySign, :GuardianAngel, :ChineseElement, :EyeColorMeaning, :GreekMythologyArchetype, :NorseMythologyPatronDeity, :EgyptianZodiacSign, :MayanZodiacSign)");

                $RegularPeopleShit->execute([":tfUN" => $userName, ":ChineseZodiacSign" => $chineseZodiacSign, ":WesternZodiacSign" => $westernZodiacSign, ":SpiritAnimal" => $spiritAnimal, ":CelticTreeZodiacSign" => $celticTreeZodiacSign, ":NativeAmericanZodiacSign" =>  $nativeAmericanZodiacSign, ":VedicAstrologySign" => $vedicAstrologySign, ":GuardianAngel" => $guardianAngel, ":ChineseElement" => $ChineseElement, ":EyeColorMeaning" => $eyeColorMeaning, ":GreekMythologyArchetype" => $GreekMythologyArchetype, ":NorseMythologyPatronDeity" => $NorseMythologyPatronDeity, ":EgyptianZodiacSign" => $EgyptianZodiacSign, ":MayanZodiacSign" => $MayanZodiacSign]);

                //create cookies
                setcookie("TfAccess", "Regular",time() + (86400 * 30));
                $_SESSION["TfAccess"] = "Free";

                setcookie("Username", $userName, time() + (86400 * 30));
                $_SESSION["Username"] = $userName;

                setcookie("Birthday", $birthdate, time() + (86400 * 365000));
                $_SESSION["Birthday"] = $birthdate;

                setcookie("Gender", $gender, time() + (86400 * 365000));
                $_SESSION["Gender"] = $gender;

                setcookie("Nickname", $nickName, time() + (86400 * 365000));
                $_SESSION["Nickname"] = $nickName;

                setcookie("Email", $email, time() + (86400 * 365));    
                $_SESSION["Email"] = $email;

                setcookie("ChineseZodiacSign", $chineseZodiacSign, time() + (86400 * 365));
                $_SESSION["ChineseZodiacSign"] = $chineseZodiacSign;

                setcookie("WesternZodiacSign", $westernZodiacSign,time() + (86400 * 365));
                $_SESSION["WesternZodiacSign"] = $westernZodiacSign;

                setcookie("SpiritAnimal",$spiritAnimal,time() + (86400 * 365));
                $_SESSION["SpiritAnimal"] = $spiritAnimal;

                setcookie("CelticTreeZodiacSign", $celticTreeZodiacSign, time() + (86400 * 365));
                $_SESSION["CelticTreeZodiacSign"] = $celticTreeZodiacSign;

                setcookie("NativeAmericanZodiacSign", $nativeAmericanZodiacSign,time() + (86400 * 365));  
                $_SESSION["NativeAmericanZodiacSign"] = $nativeAmericanZodiacSign;

                setcookie("VedicAstrologySign", $vedicAstrologySign,time() + (86400 * 365));
                $_SESSION["VedicAstrologySign"] = $vedicAstrologySign;

                setcookie("GuardianAngel", $guardianAngel,time() + (86400 * 365));
                $_SESSION["GuardianAngel"] = $guardianAngel;

                setcookie("ChineseElement", $ChineseElement,time() + (86400 * 365));
                $_SESSION["ChineseElement"] = $ChineseElement;

                setcookie("EyeColorMeaning", $eyeColorMeaning,time() + (86400 * 365));
                $_SESSION["EyeColorMeaning"] = $eyeColorMeaning;

                setcookie("GreekMythologyArchetype", $GreekMythologyArchetype, time() + (86400 * 365));
                $_SESSION["GreekMythologyArchetype"] = $GreekMythologyArchetype;

                setcookie("NorseMythologyPatronDeity", $NorseMythologyPatronDeity,time() + (86400 * 365));
                $_SESSION["NorseMythologyPatronDeity"] = $NorseMythologyPatronDeity;

                setcookie("EgyptianZodiacSign", $EgyptianZodiacSign,time() + (86400 * 365));
                $_SESSION["EgyptianZodiacSign"] = $EgyptianZodiacSign;

                setcookie("MayanZodiacSign", $MayanZodiacSign,time() + (86400 * 365));
                $_SESSION["MayanZodiacSign"] = $MayanZodiacSign;
                //create Session Variables

                //csv database version. Simple like a cookie
                $TheEntireFormFr = [$userName, $birthdate, $gender, $nickName, $email, $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign, $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign];
                $CSVfile = fopen("./TDFB/CSV/Members/regular.csv", "a");

                    if($CSVfile) {
                        if($CSVfile)
                        fputcsv($CSVfile, $TheEntireFormFr);
                        fclose($CSVfile);
                    } else {
                        $newCSVfile = fopen("./TDFB/CSV/Members/regular.csv", "w");

                        if ($newCSVfile) {
                            fputcsv($newCSVfile, ["Username", "Birthday", "Gender", "Nickname", "Email", "Chinese Zodiac Sign", "Western Zodiac Sign", "Spirit Animal", "Celtic Tree Zodiac Sign", "Native American Zodiac Sign,", "Verdic Astrology Sign", "Guardian Angel", "Chinese Element", "Eye Color Meaning", "Greek Mythology Archetype", "Norse Mythology Patron Deity", "Egyptian Zodiac Sign", "Mayan Zodiac Sign"]);
                            fputcsv($newCSVfile, $TheEntireFormFr);
                            fclose($newCSVfile);
                        }
                    }
                echo ("welcome $userName you are now a member");
            } catch (PDOException $SomeErrorFr) {
                return handleDatabaseError($SomeErrorFr);
            } finally {
                $RegularPeopleShit = null;
                $TycadomeDatabase = null;
            }
            break;
        case "vip":
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $SomeCommunityShitIguess = $TycadomeDatabase->prepare("INSERT INTO FreeLevelMembers (tfUN, tfFN, tfLN, tfNN, tfGen, tfBirth, tfEM, tfPSW, created) VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfGen, :tfBirth, :tfEM, :tfPSW, NOW())");

                $SomeCommunityShitIguess->execute([":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName, ":tfGen" => $gender, ":tfBirth" => $birthdate, ":tfEM" => $email, ":tfPSW" => $password]);
            } catch (PDOException $SomeErrorFr) {
                handleDatabaseError($SomeErrorFr);
            } finally {
                $SomeCommunityShitIguess = null;
                $TycadomeDatabase = null;
            }
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $RegularPeopleShit = $TycadomeDatabase->prepare("INSERT INTO RegularMembers (tfUN, ChineseZodiacSign, WesternZodiacSign, SpiritAnimal, CelticTreeZodiacSign, NativeAmericanZodiacSign, VedicAstrologySign, GuardianAngel, ChineseElement, EyeColorMeaning, GreekMythologyArchetype, NorseMythologyPatronDeity, EgyptianZodiacSign, MayanZodiacSign) VALUES (:tfUN, :ChineseZodiacSign, :WesternZodiacSign, :SpiritAnimal, :CelticTreeZodiacSign, :NativeAmericanZodiacSign, :VedicAstrologySign, :GuardianAngel, :ChineseElement, :EyeColorMeaning, :GreekMythologyArchetype, :NorseMythologyPatronDeity, :EgyptianZodiacSign, :MayanZodiacSign)");

                $RegularPeopleShit->execute([":tfUN" => $userName, ":ChineseZodiacSign" => $chineseZodiacSign, ":WesternZodiacSign" => $westernZodiacSign, ":SpiritAnimal" => $spiritAnimal, ":CelticTreeZodiacSign" => $celticTreeZodiacSign, ":NativeAmericanZodiacSign" =>  $nativeAmericanZodiacSign, ":VedicAstrologySign" => $vedicAstrologySign, ":GuardianAngel" => $guardianAngel, ":ChineseElement" => $ChineseElement, ":EyeColorMeaning" => $eyeColorMeaning, ":GreekMythologyArchetype" => $GreekMythologyArchetype, ":NorseMythologyPatronDeity" => $NorseMythologyPatronDeity, ":EgyptianZodiacSign" => $EgyptianZodiacSign, ":MayanZodiacSign" => $MayanZodiacSign]);
            } catch (PDOException $SomeErrorFr) {
                return handleDatabaseError($SomeErrorFr);
            } finally {
                $RegularPeopleShit = null;
                $TycadomeDatabase = null;
            }
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $VIPShitIguess = $TycadomeDatabase->prepare("INSERT INTO VIPMembers (tfUN, LoveLanguage, Birthstone, BirthFlower, BloodType, AttachmentStyle, CharismaType) VALUES (:tfUN, :LoveLanguage, :Birthstone, :BirthFlower, :BloodType, :AttachmentStyle, :CharismaType)");

                $VIPShitIguess->execute([":tfUN" => $userName, ":LoveLanguage" => $loveLanguage, ":Birthstone" => $birthStone, ":BirthFlower" =>  $birthFlower, ":BloodType" => $bloodType, ":AttachmentStyle" => $attachmentStyle, ":CharismaType" => $charismaType]);

                //create cookies
                setcookie("TfAccess", "Regular",time() + (86400 * 30));
                $_SESSION["TfAccess"] = "Free";

                setcookie("Username", $userName, time() + (86400 * 30));
                $_SESSION["Username"] = $userName;

                setcookie("Birthday", $birthdate, time() + (86400 * 365000));
                $_SESSION["Birthday"] = $birthdate;

                setcookie("Gender", $gender, time() + (86400 * 365000));
                $_SESSION["Gender"] = $gender;

                setcookie("Nickname", $nickName, time() + (86400 * 365000));
                $_SESSION["Nickname"] = $nickName;

                setcookie("Email", $email, time() + (86400 * 365));    
                $_SESSION["Email"] = $email;

                setcookie("ChineseZodiacSign", $chineseZodiacSign, time() + (86400 * 365));
                $_SESSION["ChineseZodiacSign"] = $chineseZodiacSign;

                setcookie("WesternZodiacSign", $westernZodiacSign,time() + (86400 * 365));
                $_SESSION["WesternZodiacSign"] = $westernZodiacSign;

                setcookie("SpiritAnimal",$spiritAnimal,time() + (86400 * 365));
                $_SESSION["SpiritAnimal"] = $spiritAnimal;

                setcookie("CelticTreeZodiacSign", $celticTreeZodiacSign, time() + (86400 * 365));
                $_SESSION["CelticTreeZodiacSign"] = $celticTreeZodiacSign;

                setcookie("NativeAmericanZodiacSign", $nativeAmericanZodiacSign,time() + (86400 * 365));  
                $_SESSION["NativeAmericanZodiacSign"] = $nativeAmericanZodiacSign;

                setcookie("VedicAstrologySign", $vedicAstrologySign,time() + (86400 * 365));
                $_SESSION["VedicAstrologySign"] = $vedicAstrologySign;

                setcookie("GuardianAngel", $guardianAngel,time() + (86400 * 365));
                $_SESSION["GuardianAngel"] = $guardianAngel;

                setcookie("ChineseElement", $ChineseElement,time() + (86400 * 365));
                $_SESSION["ChineseElement"] = $ChineseElement;

                setcookie("EyeColorMeaning", $eyeColorMeaning,time() + (86400 * 365));
                $_SESSION["EyeColorMeaning"] = $eyeColorMeaning;

                setcookie("GreekMythologyArchetype", $GreekMythologyArchetype, time() + (86400 * 365));
                $_SESSION["GreekMythologyArchetype"] = $GreekMythologyArchetype;

                setcookie("NorseMythologyPatronDeity", $NorseMythologyPatronDeity,time() + (86400 * 365));
                $_SESSION["NorseMythologyPatronDeity"] = $NorseMythologyPatronDeity;

                setcookie("EgyptianZodiacSign", $EgyptianZodiacSign,time() + (86400 * 365));
                $_SESSION["EgyptianZodiacSign"] = $EgyptianZodiacSign;

                setcookie("MayanZodiacSign", $MayanZodiacSign,time() + (86400 * 365));
                $_SESSION["MayanZodiacSign"] = $MayanZodiacSign;

                setcookie("LoveLanguage", $loveLanguage, time() + (86400 * 30));
                $_SESSION["LoveLanguage"] = $loveLanguage;

                setcookie("Birthstone", $birthStone, time() + (86400 * 30));
                $_SESSION["Birthstone"] = $birthStone;

                setcookie("BirthFlower", $birthFlower, time() + (86400 * 30));
                $_SESSION["BirthFlower"] = $birthFlower;

                setcookie("BloodType", $bloodType, time() + (86400 * 30));
                $_SESSION["BloodType"] = $bloodType;

                setcookie("AttachmentStyle", $attachmentStyle, time() + (86400 * 30));
                $_SESSION["AttachmentStyle"] = $attachmentStyle;

                setcookie("CharismaType", $charismaType, time() + (86400 * 30));
                $_SESSION["CharismaType"] = $charismaType;
                //create Session Variables

                //csv database version. Simple like a cookie
                $TheEntireFormFr = [$userName, $birthdate, $gender, $nickName, $email, $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign, $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $loveLanguage, $birthStone, $birthFlower, $bloodType, $attachmentStyle, $charismaType];
                $CSVfile = fopen("./TDFB/CSV/Members/vip.csv", "a");

                    if($CSVfile) {
                        if($CSVfile)
                        fputcsv($CSVfile, $TheEntireFormFr);
                        fclose($CSVfile);
                    } else {
                        $newCSVfile = fopen("./TDFB/CSV/Members/vip.csv", "w");

                        if ($newCSVfile) {
                            fputcsv($newCSVfile, ["Username", "Birthday", "Gender", "Nickname", "Email", "Chinese Zodiac Sign", "Western Zodiac Sign", "Spirit Animal", "Celtic Tree Zodiac Sign", "Native American Zodiac Sign,", "Verdic Astrology Sign", "Guardian Angel", "Chinese Element", "Eye Color Meaning", "Greek Mythology Archetype", "Norse Mythology Patron Deity", "Egyptian Zodiac Sign", "Mayan Zodiac Sign", "Love Language", "birth Stone", "Birth Flower", "Blood Type", "Attachment Style", "Charisma Type"]);
                            fputcsv($newCSVfile, $TheEntireFormFr);
                            fclose($newCSVfile);
                        }
                    }
                echo ("welcome $userName you are now a member");
            } catch (PDOException $SomeErrorFr) {
                return handleDatabaseError($SomeErrorFr);
            } finally {
                $VIPShitIguess = null;
                $TycadomeDatabase = null;
            }
            break;
        case "team":
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $SomeCommunityShitIguess = $TycadomeDatabase->prepare("INSERT INTO FreeLevelMembers (tfUN, tfFN, tfLN, tfNN, tfGen, tfBirth, tfEM, tfPSW, created) VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfGen, :tfBirth, :tfEM, :tfPSW, NOW())");

                $SomeCommunityShitIguess->execute([":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName, ":tfGen" => $gender, ":tfBirth" => $birthdate, ":tfEM" => $email, ":tfPSW" => $password]);
            } catch (PDOException $SomeErrorFr) {
                handleDatabaseError($SomeErrorFr);
            } finally {
                $SomeCommunityShitIguess = null;
                $TycadomeDatabase = null;
            }
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $RegularPeopleShit = $TycadomeDatabase->prepare("INSERT INTO RegularMembers (tfUN, ChineseZodiacSign, WesternZodiacSign, SpiritAnimal, CelticTreeZodiacSign, NativeAmericanZodiacSign, VedicAstrologySign, GuardianAngel, ChineseElement, EyeColorMeaning, GreekMythologyArchetype, NorseMythologyPatronDeity, EgyptianZodiacSign, MayanZodiacSign) VALUES (:tfUN, :ChineseZodiacSign, :WesternZodiacSign, :SpiritAnimal, :CelticTreeZodiacSign, :NativeAmericanZodiacSign, :VedicAstrologySign, :GuardianAngel, :ChineseElement, :EyeColorMeaning, :GreekMythologyArchetype, :NorseMythologyPatronDeity, :EgyptianZodiacSign, :MayanZodiacSign)");

                $RegularPeopleShit->execute([":tfUN" => $userName, ":ChineseZodiacSign" => $chineseZodiacSign, ":WesternZodiacSign" => $westernZodiacSign, ":SpiritAnimal" => $spiritAnimal, ":CelticTreeZodiacSign" => $celticTreeZodiacSign, ":NativeAmericanZodiacSign" =>  $nativeAmericanZodiacSign, ":VedicAstrologySign" => $vedicAstrologySign, ":GuardianAngel" => $guardianAngel, ":ChineseElement" => $ChineseElement, ":EyeColorMeaning" => $eyeColorMeaning, ":GreekMythologyArchetype" => $GreekMythologyArchetype, ":NorseMythologyPatronDeity" => $NorseMythologyPatronDeity, ":EgyptianZodiacSign" => $EgyptianZodiacSign, ":MayanZodiacSign" => $MayanZodiacSign]);
            } catch (PDOException $SomeErrorFr) {
                return handleDatabaseError($SomeErrorFr);
            } finally {
                $RegularPeopleShit = null;
                $TycadomeDatabase = null;
            }
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $VIPShitIguess = $TycadomeDatabase->prepare("INSERT INTO VIPMembers (tfUN, LoveLanguage, Birthstone, BirthFlower, BloodType, AttachmentStyle, CharismaType) VALUES (:tfUN, :LoveLanguage, :Birthstone, :BirthFlower, :BloodType, :AttachmentStyle, :CharismaType)");

                $VIPShitIguess->execute([":tfUN" => $userName, ":LoveLanguage" => $loveLanguage, ":Birthstone" => $birthStone, ":BirthFlower" =>  $birthFlower, ":BloodType" => $bloodType, ":AttachmentStyle" => $attachmentStyle, ":CharismaType" => $charismaType]);

            } catch (PDOException $SomeErrorFr) {
                return handleDatabaseError($SomeErrorFr);
            } finally {
                $VIPShitIguess = null;
                $TycadomeDatabase = null;
            }
            try {
                $TycadomeDatabase = TsunamiDatabaseFlow();
                $TeamShitIguess = $TycadomeDatabase->prepare("INSERT INTO TeamMembers (tfUN, BusinessPersonality, DISC, SocionicsType, LearningStyle, FinancialPersonalityType, PrimaryMotivationStyle, CreativeStyle, ConflictManagementStyle, TeamRolePreference) VALUES (:tfUN, :BusinessPersonality, :DISC, :SocionicsType, :LearningStyle, :FinancialPersonalityType, :PrimaryMotivationStyle, :CreativeStyle, :ConflictManagementStyle, :TeamRolePreference)");

                $TeamShitIguess->execute([":tfUN" => $userName, ":BusinessPersonality" => $businessPersonality, ":DISC" => $TFuserDISC, ":SocionicsType" => $socionicsType, ":LearningStyle" =>  $learningStyle, ":FinancialPersonalityType" => $financialPersonalityType, ":PrimaryMotivationStyle" =>  $primaryMotivationStyle, ":CreativeStyle" =>  $creativeStyle, ":ConflictManagementStyle" => $conflictManagementStyle, ":TeamRolePreference" => $teamRolePreference]);

                //create cookies
                setcookie("TfAccess", "Regular",time() + (86400 * 30));
                $_SESSION["TfAccess"] = "Free";

                setcookie("Username", $userName, time() + (86400 * 30));
                $_SESSION["Username"] = $userName;

                setcookie("Birthday", $birthdate, time() + (86400 * 365000));
                $_SESSION["Birthday"] = $birthdate;

                setcookie("Gender", $gender, time() + (86400 * 365000));
                $_SESSION["Gender"] = $gender;

                setcookie("Nickname", $nickName, time() + (86400 * 365000));
                $_SESSION["Nickname"] = $nickName;

                setcookie("Email", $email, time() + (86400 * 365));    
                $_SESSION["Email"] = $email;

                setcookie("ChineseZodiacSign", $chineseZodiacSign, time() + (86400 * 365));
                $_SESSION["ChineseZodiacSign"] = $chineseZodiacSign;

                setcookie("WesternZodiacSign", $westernZodiacSign,time() + (86400 * 365));
                $_SESSION["WesternZodiacSign"] = $westernZodiacSign;

                setcookie("SpiritAnimal",$spiritAnimal,time() + (86400 * 365));
                $_SESSION["SpiritAnimal"] = $spiritAnimal;

                setcookie("CelticTreeZodiacSign", $celticTreeZodiacSign, time() + (86400 * 365));
                $_SESSION["CelticTreeZodiacSign"] = $celticTreeZodiacSign;

                setcookie("NativeAmericanZodiacSign", $nativeAmericanZodiacSign,time() + (86400 * 365));  
                $_SESSION["NativeAmericanZodiacSign"] = $nativeAmericanZodiacSign;

                setcookie("VedicAstrologySign", $vedicAstrologySign,time() + (86400 * 365));
                $_SESSION["VedicAstrologySign"] = $vedicAstrologySign;

                setcookie("GuardianAngel", $guardianAngel,time() + (86400 * 365));
                $_SESSION["GuardianAngel"] = $guardianAngel;

                setcookie("ChineseElement", $ChineseElement,time() + (86400 * 365));
                $_SESSION["ChineseElement"] = $ChineseElement;

                setcookie("EyeColorMeaning", $eyeColorMeaning,time() + (86400 * 365));
                $_SESSION["EyeColorMeaning"] = $eyeColorMeaning;

                setcookie("GreekMythologyArchetype", $GreekMythologyArchetype, time() + (86400 * 365));
                $_SESSION["GreekMythologyArchetype"] = $GreekMythologyArchetype;

                setcookie("NorseMythologyPatronDeity", $NorseMythologyPatronDeity,time() + (86400 * 365));
                $_SESSION["NorseMythologyPatronDeity"] = $NorseMythologyPatronDeity;

                setcookie("EgyptianZodiacSign", $EgyptianZodiacSign,time() + (86400 * 365));
                $_SESSION["EgyptianZodiacSign"] = $EgyptianZodiacSign;

                setcookie("MayanZodiacSign", $MayanZodiacSign,time() + (86400 * 365));
                $_SESSION["MayanZodiacSign"] = $MayanZodiacSign;

                setcookie("LoveLanguage", $loveLanguage, time() + (86400 * 30));
                $_SESSION["LoveLanguage"] = $loveLanguage;

                setcookie("Birthstone", $birthStone, time() + (86400 * 30));
                $_SESSION["Birthstone"] = $birthStone;

                setcookie("BirthFlower", $birthFlower, time() + (86400 * 30));
                $_SESSION["BirthFlower"] = $birthFlower;

                setcookie("BloodType", $bloodType, time() + (86400 * 30));
                $_SESSION["BloodType"] = $bloodType;

                setcookie("AttachmentStyle", $attachmentStyle, time() + (86400 * 30));
                $_SESSION["AttachmentStyle"] = $attachmentStyle;

                setcookie("CharismaType", $charismaType, time() + (86400 * 30));
                $_SESSION["CharismaType"] = $charismaType;

                setcookie("BusinessPersonality", $businessPersonality, time() + (86400 * 30));
                $_SESSION["BusinessPersonality"] = $businessPersonality;

                setcookie("DISC", $TFuserDISC, time() + (86400 * 30));
                $_SESSION["DISC"] = $TFuserDISC;

                setcookie("SocionicsType", $socionicsType, time() + (86400 * 30));
                $_SESSION["SocionicsType"] = $socionicsType;

                setcookie("LearningStyle", $learningStyle, time() + (86400 * 30));
                $_SESSION["LearningStyle"] = $learningStyle;

                setcookie("FinancialPersonalityType", $financialPersonalityType, time() + (86400 * 30));
                $_SESSION["FinancialPersonalityType"] = $financialPersonalityType;

                setcookie("PrimaryMotivationStyle", $primaryMotivationStyle, time() + (86400 * 30));
                $_SESSION["PrimaryMotivationStyle"] = $primaryMotivationStyle;

                setcookie("CreativeStyle", $creativeStyle, time() + (86400 * 30));
                $_SESSION["CreativeStyle"] = $creativeStyle;

                setcookie("ConflictManagementStyle", $conflictManagementStyle, time() + (86400 * 30));
                $_SESSION["ConflictManagementStyle"] = $conflictManagementStyle;

                setcookie("TeamRolePreference", $teamRolePreference, time() + (86400 * 30));
                $_SESSION["TeamRolePreference"] = $teamRolePreference;
                //create Session Variables

                //csv database version. Simple like a cookie
                $TheEntireFormFr = [$userName, $birthdate, $gender, $nickName, $email, $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign, $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $loveLanguage, $birthStone, $birthFlower, $bloodType, $attachmentStyle, $charismaType, $businessPersonality, $TFuserDISC, $socionicsType, $learningStyle, $financialPersonalityType, $primaryMotivationStyle, $creativeStyle, $conflictManagementStyle, $teamRolePreference];
                $CSVfile = fopen("./TDFB/CSV/Members/team.csv", "a");

                    if($CSVfile) {
                        if($CSVfile)
                        fputcsv($CSVfile, $TheEntireFormFr);
                        fclose($CSVfile);
                    } else {
                        $newCSVfile = fopen("./TDFB/CSV/Members/team.csv", "w");

                        if ($newCSVfile) {
                            fputcsv($newCSVfile, ["Username", "Birthday", "Gender", "Nickname", "Email", "Chinese Zodiac Sign", "Western Zodiac Sign", "Spirit Animal", "Celtic Tree Zodiac Sign", "Native American Zodiac Sign,", "Verdic Astrology Sign", "Guardian Angel", "Chinese Element", "Eye Color Meaning", "Greek Mythology Archetype", "Norse Mythology Patron Deity", "Egyptian Zodiac Sign", "Mayan Zodiac Sign", "Love Language", "birth Stone", "Birth Flower", "Blood Type", "Attachment Style", "Charisma Type", "Business Personality", "DISC", "Socionics Type", "Learning Style", "Financial Personality Type", "Primary Motivation Style", "Creative Style", "Conflict Management Style", "Team Role Preference"]);
                            fputcsv($newCSVfile, $TheEntireFormFr);
                            fclose($newCSVfile);
                        }
                    }
                echo ("welcome $userName you are now a member");
            } catch (PDOException $SomeErrorFr) {
                return handleDatabaseError($SomeErrorFr);
            } finally {
                $TeamShitIguess = null;
                $TycadomeDatabase = null;
            }
            break;
    }
}
?>