<?php

require 'config.php';
header("Access-Control-Allow-Origin: *");

// Allow specific headers
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

// Allow specific methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

/* Functions */
// Input sanitization
function tsunamiInput($inputData) {
    return htmlspecialchars(trim($inputData), ENT_QUOTES, 'UTF-8');
}

// Error handling
function handleDatabaseErrors($exception) {
    error_log($exception->getMessage(), 3, "tferror.log");
    // Provide a generic message for end users
    echo "An error occurred. Please try again later.";
}

// Validate input
function validateInput($inputName, $inputArray) {
    if (isset($inputArray[$inputName]) && !empty($inputArray[$inputName])) {
        $inputValue = tsunamiInput($inputArray[$inputName]);
        // Adjust regex as needed
        if (preg_match("/^[a-zA-Z0-9-']+$/", $inputValue)) {
            return $inputValue;
        } else {
            return "Invalid characters in $inputName.";
        }
    } else {
        return "You have to provide your $inputName.";
    }
}

// Main function
function addToDatabase() {
    session_start(); // Ensure session_start() is at the beginning

if(!empty($_POST["NavUserName"])) {
    $tfUsername = validateInput("NavUserName", $_POST);
    $tfPassword = validateInput("NavPassword", $_POST);
} else if(!empty($_REQUEST["phpnun"])) {
    $tfUsername = validateInput("phpnun", $_REQUEST);
    $tfPassword =  validateInput("phpnpsw", $_REQUEST);
}


    // Check for validation errors
    if (is_string($tfUsername) && is_string($tfPassword)) {
        $dsn = "mysql:host=" . NANO_HOST . ";dbname=" . NANO_DB;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, NANO_USER, NANO_PSW, $options);

            // Check user
            $stmt = $pdo->prepare("SELECT * FROM FreeLevelMembers WHERE tfUN = :username");
            $stmt->bindParam(':username', $tfUsername, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user !== null) {
                $_SESSION["TsunamiGangUserName"] = $user['tfUN'];
                echo htmlspecialchars($tfUsername) . " is now logged in.";
            } else {
                echo "Unsuccessful login attempt.";
            }
        } catch (PDOException $e) {
            handleDatabaseErrors($e);
        }
    } else {
        // Output validation errors
        echo htmlspecialchars($tfUsername) . "<br>" . htmlspecialchars($tfPassword);
    }
}

// Request method handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    addToDatabase();
} else {
    echo "The server sent the wrong request.";
}
?>