<?php
//Thoughts 
//Post THoughts
function PostingThoughtsFr(){
    $TycadomeDatabase = TsunamiDatabaseFlow();
    try {
        /*
            CREATE TABLE tfThoughts(
                tfUN VARCHAR(50) PRIMARY KEY,
                TsunamiThought VARCHAR(200),
                ThoughtTime DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(tfUN) REFERENCES FreeLevelMembers(tfUN)
            );
        */
        if (!empty($_POST["tfThoughtArea"])) {
            $TFgangThoughts = TsunamiInput($_POST["tfThoughtArea"]);
            if (preg_match("/^[a-zA-Z-']*$/", $TFgangThoughts)) {
                $tfVerifyThought = filter_var($TFgangThoughts, FILTER_DEFAULT);
                if ($tfVerifyThought) {
                    if (is_string($tfVerifyThought)) {
                        $theThought = $tfVerifyThought;
                        $ThotUser = $_SESSION["TsunamiGangUserName"];
                    } else {
                        $theThoughtErr = "You can only use letters and whitespace";
                        $theThought = null;
                    }
                } else {
                    $theThoughtErr = "You can only use letters and whitespace";
                    $theThought = null;
                }
            } else {
                $theThoughtErr = "You can only use letters and whitespace";
                $theThought = null;
            }
        } else if (!empty($_REQUEST["tfthought"])) {
            $tfGthots = TsunamiInput($_REQUEST["tfthought"]);
            if (preg_match("/^[a-zA-Z-']*$/", $tfGthots)) {
                $tfVerifyThought = filter_var($tfGthots, FILTER_DEFAULT);
                if ($tfVerifyThought) {
                    if (is_string($tfVerifyThought)) {
                        $theThought = $tfVerifyThought;
                        $ThotUser = $_SESSION["TsunamiGangUserName"];
                    } else {
                        $theThoughtErr = "You can only use letters and whitespace";
                        $theThought = null;
                    }
                } else {
                    $theThoughtErr = "You can only use letters and whitespace";
                    $theThought = null;
                }
            } else {
                $theThoughtErr = "You can only use letters and whitespace";
                $theThought = null;
            }
        } else {
            echo ("The THoughts are failing somewhere during the text validation");
        }

        $TycadomeNewMember = $TycadomeDatabase->prepare("INSERT INTO tfThoughts (tfUN, TsunamiThought, ThoughtTime) VALUES (?, ?)");

        $TycadomeNewMember->bindParam(0, $ThotUser, PDO::PARAM_STR);
        $TycadomeNewMember->bindParam(1, $theThought, PDO::PARAM_STR);

        try {
            $TycadomeNewMember->execute();
        } catch (PDOException $e) {
            handleDatabaseError($e);
        }
    } catch (PDOException $e) {
        handleDatabaseError($e);
    } finally {
        $TycadomeDatabase = null;
    }
}
//Post Thoughts End

//Get Thoughts
// Function to get thoughts
function WorkgetThoughts(){
    try {
        $TycadomeDatabase = TsunamiDatabaseFlow();
        // Query database
        $TycadomeThoughts = $TycadomeDatabase->query("SELECT * FROM tfThoughts");

        // Fetch all results
        $tfThoughtRow = $TycadomeThoughts->fetchAll(PDO::FETCH_ASSOC);

        if ($tfThoughtRow !== null) {
            $Thotid = array();
            foreach ($tfThoughtRow as $tfList) {
                $ThoughtUserName = $tfList['tfUN'];
                $tfThoughts = $tfList['TsunamiThought'];
                $tfThoughtTime = $tfList['ThoughtTime'];
                $ThoughtsArray = array($ThoughtUserName, $tfThoughts, $tfThoughtTime);
                array_push($Thotid, $ThoughtsArray);
            }
            // Output JSON
            echo "data: " . json_encode($Thotid) . "\n\n";
        } else {
            echo "data: The tfThoughts Database is empty.\n\n";
        }
    } catch (PDOException $e) {
        handleDatabaseError($e);
    } finally {
        // Close the database connection
        $TycadomeDatabase = null;
        flush();
    }
}
//Gets Thoughts Ends
//Database Ends
?>