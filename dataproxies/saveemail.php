<?php
# see http://vizzuality.googlecode.com/svn-history/r3486/trunk/otrobache.com/services/fusiontableslib.php
session_start();
require_once '../lib/google-api-php-client/src/Google_Client.php';
require_once '../lib/google-api-php-client/src/contrib/Google_FusiontablesService.php';
require_once '../inc/auth.php';

if (isset($_POST["email"])){$email = str_replace(";","",$_POST["email"]);}else{$email = "test: delete!";}
if (isset($_SESSION["session"])){$session = str_replace(";","",$_SESSION["session"]);}else{$session = "0000000000";}

$client = get_client();

$service = new Google_FusiontablesService($client);
$table_key = ""; //unique key identifying google fusion table

$date_string = date("m/d/y g:i A", time());

$query = "INSERT INTO $table_key (Date, Email, Session) VALUES ('".$date_string."', '".$email."', '".$session."')";

if ($query != '') {
    $result = $service->query->sql($query);
    if ($result["kind"] == "fusiontables#sqlresponse") {
        $rows = $result["rows"];
        echo $rows[0][0];
    }
};

?> 