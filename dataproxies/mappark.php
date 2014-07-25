<?php
# see http://vizzuality.googlecode.com/svn-history/r3486/trunk/otrobache.com/services/fusiontableslib.php
session_start();
# see http://vizzuality.googlecode.com/svn-history/r3486/trunk/otrobache.com/services/fusiontableslib.php
require_once "../inc/fusiontableslib.php";
require_once "../inc/falk_functions.inc";

if (isset($_GET["schoolid"])){$school_id = $_GET["schoolid"];} else {$school_id = 0;}
# echo $school_id;

#echo "test".$_SERVER['HTTP_REFERER'];

$token = get_token();
# echo $token."<br>";
$ft = new FusionTable($token);

# use https://www.google.com/fusiontables/api/query?sql=SELECT * FROM 2212812
# to test queries
# replace ****** below with unique fusion table key
$query = "SELECT SchoolCode, FullName, geometry FROM ************************** WHERE SchoolCode=".$school_id;
#print $query;
$out = $ft -> query($query);
# Writing works ==> Great
# $ft -> query("INSERT INTO 2212812 (Comment) VALUES ('TEST')");

#echo count($out);

$json = '{"parkloc":[';
#$json = '';
for ($i=0; $i<count($out); $i++)
{
    $arr = $out[$i];
    $arr["geometry"] = str_replace("<Point><coordinates>", "", $arr["geometry"]);
    $arr["geometry"] = str_replace("</coordinates></Point>", "", $arr["geometry"]);
    # $aux = split(",", $arr["geometry"]);
    #$arr["lng"] = $aux[0];
    #$arr["lat"] = $aux[1];
    $json = $json.json_encode($arr).",";
    #echo $out[$i]["ROWID"]."&nbsp;".$out[$i]["Name"]."&nbsp;".$out[$i]["geometry"]."<br>";
}
$json = rtrim($json,",")."]}";
echo $json;
# include store output here

?> 