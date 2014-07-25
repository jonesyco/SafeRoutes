<?php
$filename = "../staticlayers/Suggested_Routes.json";

if (isset($_GET["schoolid"])) { $school_id = $_GET["schoolid"]; } else { $school_id = 0; }
if (isset($_GET["all"])) { $all = $_GET["all"]; } else { $all = "false"; } 

# make sure that geojson is well-formed
# see a
$geojson = json_decode(file_get_contents($filename));

#  var_dump($geojson);

$map = array();
foreach($geojson->features as $obj)
    if ($obj->properties->SchoolCode == $school_id || $all == "true") {
        $map[] = array($obj->properties->FullName, $obj);
    }
    
$json = '{"routes":[';
foreach ( $map as $feature) {
    $arr["SchoolCode"] = $feature[1] -> properties->SchoolCode;
    $arr["Geometry"] = $feature[1] -> geometry;
    $json .= json_encode($arr).",";
}

# this is not a correct geojson but has valid geometry representations
# could be migrated but would have more overhead
$json = rtrim($json,",")."]}";
echo $json;
?> 