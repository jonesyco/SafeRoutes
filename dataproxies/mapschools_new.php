<?php
include("../lib/geophp/geoPHP.inc");
$filename = "../staticlayers/schools_new.json"; 
$geojson = json_decode(file_get_contents($filename));
#print_r($geojson);
#foreach ($geojson -> features -> feature as $feature) {
#    print_r($feature);
#    echo "<br><br>";
#}

$json = '{"schools":[';

# cool trick
foreach($geojson->features as $obj)
    $map[] = array($obj->properties->FullName, $obj);
sort($map);

foreach ( $map as $feature) {
    $arr["OBJECTID"] = $feature[1]->properties->SchoolCode;
    $arr["Name"] = $feature[1]->properties->FullName;
    $geom = geoPHP::load($feature[1]->geometry,'json');
    $centroid = $geom->getCentroid();
    $arr["geometry"] = str_replace(array("POINT (",")"),"", $centroid->out('wkt'));
    $arr["geometry"] = str_replace(" ",",",$arr["geometry"]);
    $json = $json.json_encode($arr).",";
}
# migrate output to real geojson
$json = rtrim($json,",")."]}";
echo $json;
# include store output here
?>