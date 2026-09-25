<?php

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true) ?? $_POST ?? [];
$method = $_SERVER['REQUEST_METHOD'];

?>