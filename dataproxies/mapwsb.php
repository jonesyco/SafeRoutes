<?php
include("../lib/geophp/geoPHP.inc");
$filename = "../staticlayers/WalkingSchoolBus.kml"; 

if (isset($_GET["schoolid"])) { $school_id = $_GET["schoolid"]; } else { $school_id = 0; }
if (isset($_GET["all"])) { $all = $_GET["all"]; } else { $all = "false"; } # careful $all is not boolean!!!

# from http://stackoverflow.com/questions/1246732/parsing-xml-cdata-with-php
$str = file_get_contents("../staticlayers/WalkingSchoolBus.kml");
$xmlDoc = new DOMDocument();
$xmlDoc->loadXML($str);

$arr = array();
$json = '{"routes":[';

//get elements from "<channel>"
$placemark = $xmlDoc->getElementsByTagName('Placemark');
foreach ($placemark as $p) {
    $arr["SchoolCode"] = $p->getElementsByTagName('name')->item(0)->nodeValue;
    if ($school_id == $arr["SchoolCode"] || $all == "true") {
        $arr["SchoolCode"] = $p->getElementsByTagName('name')->item(0)->nodeValue;
        $description = $p->getElementsByTagName('description')->item(0)->nodeValue;
        $description_cleaned = strip_tags($description,"<br><b><i>");
        $arr["Description"] = $description_cleaned;
        $geom = $p->getElementsByTagName('Point')->item(0);
        if (!is_null($geom)) {
            $type = "Point";
        } else {
            $geom = $p->getElementsByTagName('LineString')->item(0);
            if (!is_null($geom)) {
                $type = "LineString";
            }
        }    
        if (!is_null($geom)) {
            $coordinates = explode("\n",trim($geom->getElementsByTagName('coordinates')->item(0)->nodeValue));
            $coords = array();
            for ($i=0; $i<sizeof($coordinates); $i++) {
                $coords[] = explode(",",$coordinates[$i]);
            }
            $arr["Geometry"]->type = $type;
            $arr["Geometry"]->coordinates = $coords;
            $json .= json_encode($arr).",";
        }
    }
}

$json = rtrim($json,",")."]}";
echo $json;
?> 