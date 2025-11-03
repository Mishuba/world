<?php
//Put the first array I can find here
//Infinityfree Database Options
$tfSQLoptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

//Tsunami Flow Radio 
    //create the radioPlayer Array
$sentToJsArray = array(
    array(array(), array(), array(), array()), // Rizz
    array(array(), array(), array(), array()), // Dancing
    array(), // After Club
    array(array(), array(), array()), // Sex Foreplay Sex Cuddles
    array(array(), array(), array()), // Love
    array(array(), array(), array()), // Family
    array(array(), array(), array()), // Inspiration
    array(array(), array(), array()), // History
    array(array(), array(), array(), array(), array(), array()), // Politics
    array(array(), array(), array()), // Gaming
    array(), // Comedy
    array(), // All Music
    array(array(), array(), array(), array()), // Literature
    array(array(), array(), array(), array()), // Sports
    array(array(), array(), array(), array()), // Tech
    array(array(), array(), array(), array()), // Science
    array(array(), array(), array(), array()), // Real Estate
    array(), // DJshuba
    array(array(), array(), array(), array(), array(), array()), // Film
    array(array(), array(), array(), array()), // Fashion
    array(array(), array(), array(), array()), // Business
    array(), // Hustlin
    array(), // Pregame
    array()  // Outside
);
    //Radio Player Array Ends
//Tsunami Flow Radio Ends

//Stripe
    //Tax ID
$taxIdTypes = [
    "DE" => "eu_vat",
    "FR" => "eu_vat",
    "GB" => "eu_vat",
    "US" => "ein",
    "AU" => "au_abn",
    "BR" => "br_cnp",
    "CA" => "ca_bn",
    "IN" => "in_gst",
    "MX" => "mx_rfc",
    "NO" => "no_voec",
    "NZ" => "nz_gst",
    "CH" => "ch_vat"
];
//Stripe Ends

//Printful

//Printful Ends
//Put the last array I can find here
?>