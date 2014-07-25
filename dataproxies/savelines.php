<?php
# echo date('m/d/Y h:i:s a', time());
# print file_get_contents('/home/solanosr2s/da6f3f97ddaa62806dcfb784c60e42dafc6906da-privatekey.p12');

# see http://vizzuality.googlecode.com/svn-history/r3486/trunk/otrobache.com/services/fusiontableslib.php
session_start();
require_once '../lib/google-api-php-client/src/Google_Client.php';
require_once '../lib/google-api-php-client/src/contrib/Google_FusiontablesService.php';
require_once '../inc/auth.php';

if (isset($_POST["coordinates"])){$coordinates = str_replace(";","",$_POST["coordinates"]);}else{$coordinates="38.288889, -122.458889";}
if (isset($_POST["comment"])){$comment = str_replace(";","",$_POST["comment"]);}else{$comment = "test: delete";}
if (isset($_POST["school"])){$school = str_replace(";","",$_POST["school"]);}else{$school = "0";}
if (isset($_POST["v100"])){$v100 = str_replace(";","",$_POST["v100"]);}else{$v100 = "";}
if (isset($_POST["dbid"])){$dbid = str_replace(";","",$_POST["dbid"]);}else{$dbid = "";}
if (isset($_POST["delete"])){$delete = str_replace(";","",$_POST["delete"]);}else{$delete = "";}
if (isset($_POST["type"])){$type = str_replace(";","",$_POST["type"]);}else{$type = "";}
if (isset($_POST["mode"])){$mode= str_replace(";","",$_POST["mode"]);}else{$mode = "";}
if (isset($_POST["school_name"])){$school_name= str_replace(";","",$_POST["school_name"]);}else{$school_name = "";}
if (isset($_SESSION["session"])){$session = str_replace(";","",$_SESSION["session"]);}else{$session = "0000000000";}

$client = get_client();

$service = new Google_FusiontablesService($client);
$table_key = ""; //unique key identifying google fusion table

$date_string = date("m/d/y g:i A", time());

if ($dbid=="") {
    $query = "INSERT INTO $table_key (";
    $query .= "geometry, "; #1
    $query .= "Date, "; #2
    $query .= "Comment, "; #3
    $query .= "Assoc_school, ";
    $query .= "SchoolCode, "; #4
    $query .= "SchoolName, "; #5
    $query .= "Type, "; #6
    $query .= "Mode, "; #7
    $query .= "v100, "; #8
    $query .= "Session"; #9
    $query .= ") VALUES (";
    $query .= "'".$coordinates."', "; #1
    $query .= "'".$date_string."', "; #2
    $query .= "'".$comment."', "; #3
    $query .= "'".$school."', "; #4
    $query .= "'".$school."', "; #4
    $query .= "'".$school_name."', "; #5
    $query .= "'".$type."', "; #6
    $query .= "'".$mode."', "; #7
    $query .= "'".$v100."', "; #8
    $query .= "'".$session."')"; #9
} else {
    if ($delete == "1") {
        $query = "DELETE FROM $table_key WHERE ROWID='".trim($dbid)."'";
    } else {
        $query = "UPDATE $table_key SET ";
        $query .= "geometry = '".$coordinates."',";
        $query .= "Date = '".$date_string."',";
        $query .= "v100 = '".$date_string."',";
        $query .= "Comment = '".$comment."',";
        $query .= "Mode = '".$mode."',";
        $query .= "Type = '".$type."',";
        $query .= "Session = '".$type."' ";
        $query .= " WHERE ROWID = '".trim($dbid)."'";
    }
}

if ($query != '') {
    $result = $service->query->sql($query);
    if ($result["kind"] == "fusiontables#sqlresponse") {
        $rows = $result["rows"];
        echo $rows[0][0];
    }
};

?> 