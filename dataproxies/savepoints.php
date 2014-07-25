<?php
session_start();
require_once '../lib/google-api-php-client/src/Google_Client.php';
require_once '../lib/google-api-php-client/src/contrib/Google_FusiontablesService.php';
require_once '../inc/auth.php';

# remove ; in order to prevent SQL injection
if (isset($_POST["coordinates"])){$coordinates = str_replace(";","",$_POST["coordinates"]);}else{$coordinates="38.288889, -122.458889";}
if (isset($_POST["comment"])){$comment = str_replace(";","",$_POST["comment"]);}else{$comment = "test: delete";}
if (isset($_POST["school"])){$school = str_replace(";","",$_POST["school"]);}else{$school = "0";}
if (isset($_POST["route"])){$route = str_replace(";","",$_POST["route"]);}else{$route = "0";}
if (isset($_POST["type"])){$type = str_replace(";","",$_POST["type"]);}else{$type = "";}
if (isset($_POST["dbid"])){$dbid = str_replace(";","",$_POST["dbid"]);}else{$dbid = "";}
if (isset($_POST["delete"])){$delete = str_replace(";","",$_POST["delete"]);}else{$delete = "0";}
if (isset($_POST["school_name"])){$school_name = str_replace(";","",$_POST["school_name"]);}else{$school_name = "0";}
if (isset($_SESSION["session"])){$session = str_replace(";","",$_SESSION["session"]);}else{$session = "0000000000";}

$client = get_client();
$service = new Google_FusiontablesService($client);
$table_key = ""; //unique key identifying google fusion table

for ($i=11; $i<65; $i++) {
    $vname = "v".$i;
    if (isset($_POST[$vname])){$$vname = str_replace(";","",$_POST[$vname]);}else{$$vname = "";}
}

# convert to kml

$coo = explode(", ", $coordinates);
$kml = "<Point><coordinates>".$coo[1].",".$coo[0]."</coordinates></Point>";
# YES, coordinates get switched around

$date_string = date("m/d/y g:i A", time());

#$dbid="3656";
#$v21 = "fair";

if ($dbid=="") {
    $query = "INSERT INTO $table_key (";
    $query .= "Coordinates, ";
    $query .= "Date, ";
    $query .= "Comment, ";
    $query .= "SchoolCode, ";
    $query .= "SchoolName, ";
    $query .= "Assoc_route, ";
    $query .= "Comment_type, ";
    for ($i=11; $i<65; $i++) {
        $vname = "v".$i;
        if ($$vname != "") {
            $query .= $vname.", ";
        }
    }
    $query .= "Session";
    $query .= ") VALUES (";
    $query .= "'".$kml."', ";
    $query .= "'".$date_string."', ";
    $query .= "'".$comment."', ";
    $query .= "'".$school."', ";
    $query .= "'".$school_name."', "; 
    $query .= "'".$route."', ";
    $query .= "'".$type."', ";
    for ($i=11; $i<65; $i++) {
        $vname = "v".$i;
        if ($$vname != "") {
            $query .= "'".$$vname."', ";
        }
    }
    $query .= "'".$session."')";
} else {
    if ($delete == "1") {
        $query = "DELETE FROM $table_key WHERE ROWID='".trim($dbid)."'";
        //echo "delete";
    } else {
        $query = "UPDATE $table_key SET ";
        $query .= "Coordinates = '".$kml."', ";
        $query .= "Date = '".$date_string."' ,";
        for ($i=11; $i<65; $i++) {
            $vname = "v".$i;
            if ($$vname != "") {
                $query .= $vname."='".$$vname."', ";
            }
        }
        $query .= "Comment = '".$comment."' ";
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