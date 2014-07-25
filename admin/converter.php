<?php
# url for shapefileconverter
$test = False;
$url = "http://ogre.adc4gis.com/convertJson";

session_start();
require_once '../lib/google-api-php-client/src/Google_Client.php';
require_once '../lib/google-api-php-client/src/contrib/Google_FusiontablesService.php';
require_once '../inc/auth.php';

function kml($res) {
    $template = file_get_contents("template.kml");
    $ind = "   ";
    $columns = $res["columns"];
    $output = "";
    foreach ($res["rows"] as $row) {
        $placemark = "<Placemark>\n<styleUrl>#ddl</styleUrl>";
        $data = "";
        $extended_data = "$ind<ExtendedData>\n";
        for ($i = 0; $i < sizeof($columns); $i++) {
            if ($columns[$i] == "rowid") {
                $data .= $ind."<name>".$row[$i]."</name>\n";
            } elseif ($columns[$i] == "Comment") {
                $data .= $ind."<description>".$row[$i]."</description>\n";
            } elseif ($columns[$i] == "geometry" || $columns[$i] == "line" || $columns[$i] == "Coordinates") {
                if (is_array($row[$i]) && array_key_exists("geometry", $row[$i])) {
                    $type = $row[$i]["geometry"]["type"];
                    $data .= $ind."<".$type.">\n";
                    $data .= $ind.$ind."<coordinates>\n";
                    if ($columns[$i] == "Coordinates") {
                        $data .= $ind.$ind.$row[$i]["geometry"]["coordinates"][0].",".$row[$i]["geometry"]["coordinates"][1].",0\n";
                    } else {
                        foreach ($row[$i]["geometry"]["coordinates"] as $coord) {
                            $data .= $ind.$ind.$coord[0].",".$coord[1].",0\n";
                        }
                    }
                    $data .= $ind.$ind."</coordinates>\n";
                    $data .= $ind."</".$type.">\n";
                    # var_dump($row[$i]["geometry"]["coordinates"][1]);
                }
            } else {
                $extended_data .= $ind.$ind."<Data name=\"".$columns[$i]."\">\n";
                $extended_data .= $ind.$ind.$ind."<displayName>".$columns[$i]."</displayName>\n";
                $extended_data .= $ind.$ind.$ind."<value>".$row[$i]."</value>\n";
                $extended_data .= $ind.$ind."</Data>\n";
            }
        }
        $placemark .= $data.$extended_data.$ind."</ExtendedData>\n</Placemark>\n";
        $output .= $placemark;
    }
    echo str_replace("## put Placemarks here ##",$output, $template);
}

function geojson($res){
    #$template = file_get_contents("template.kml");
    #$ind = "   ";
    $columns = $res["columns"];
    $output = '{"type": "FeatureCollection", "features": [';
    foreach ($res["rows"] as $row) {
        $output .= '{ "type": "Feature", "properties": {';
        for ($i = 0; $i < sizeof($columns); $i++) {
            if ($columns[$i] == "geometry" || $columns[$i] == 'Coordinates' ) {
                if (is_array($row[$i]) && array_key_exists("geometry", $row[$i]) && sizeof($row[$i]["geometry"]["coordinates"])>0) {
                    $geometry = '"geometry":{"type":"'.$row[$i]["geometry"]["type"];
                    $geometry .= '","coordinates": [';
                    if ($row[$i]["geometry"]["type"] == 'Point') {
                        $geometry .= $row[$i]["geometry"]["coordinates"][0].', '.$row[$i]["geometry"]["coordinates"][1].', ';
                    } else {
                        foreach ($row[$i]["geometry"]["coordinates"] as $coord) {
                            $geometry .= '['.$coord[0].', '.$coord[1].'], ';
                        }
                    }
                    $geometry = rtrim($geometry, ', ');
                    $geometry .= ']}';
                }
            } else {
                $output .= '"'.$columns[$i].'":"'.$row[$i].'",';
            }
        }
        $output = rtrim($output, ',');
        $output .= '},'.$geometry;
        $output.= '},';
    }
    $output = rtrim($output, ',');
    $output .= ']}';
    return $output;
}

$client = get_client();

$service = new Google_FusiontablesService($client);

if(isset($_GET["type"])) { $type = $_GET["type"]; } else { $type = "routes"; }
if(isset($_GET["format"])) { $format = $_GET["format"]; } else { $format = "json"; } 
if(isset($_GET["version"])) { $version = $_GET["version"]; } else { $version = "new"; }

if (!$test) {
    if ($format == 'json') {
        header('Content-disposition: attachment; filename='.$type.'_'.$version.'.json');
        header('Content-type: application/json');
    }
    elseif ($format == 'kml') {
        header('Content-disposition: attachment; filename='.$type.'_'.$version.'.kml');
        header('Content-type: text/xml');
    }
    elseif ($format == 'shapefile') {
        header('Content-disposition: attachment; filename='.$type.'_'.$version.'.zip');
        header('Content-type: application/octet-stream'); 
    }
    else {
        header('Content-type: text/html'); 
    }
}
 
# replace ****** below with unique fusion table keys
$table_key["routes"] = array(
    "new" => "****************************************"
);
$table_key["comments"] = array(
    "new" => "****************************************"
);
$table_key["email"] = array(
    "new" => "*****************************************"
);



$sql = "DESCRIBE ".$table_key[$type][$version];
#echo $sql;
$result = $service->query->sql($sql);
if ($result["kind"] == "fusiontables#sqlresponse") {
    $rows = $result["rows"];
    #var_dump($rows);
    $fields = "ROWID";
    foreach ($rows as $r) {
        $fields .= ", ".$r[1];
    }
    # echo "<br>".$fields."<br>";
    $sql = "SELECT ".$fields." FROM ".$table_key[$type][$version];
    # $sql = "SELECT ROWID, Coordinates, Comment, Date, Session, Assoc_school, v13 FROM ".$table_key[$type][$version];
    # print $sql;
    $result = $service->query->sql($sql);
    if ($result["kind"] == "fusiontables#sqlresponse") {
        if ($format == 'shapefile' || $format == "json") {
            $json = geojson($result);
            if ($format == "shapefile") {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'json='.$json);
                $result = curl_exec($ch);
                echo $result;
                curl_close($ch);
            } else {
                echo $json;
            }
        } else {
            $kml = kml($result);
            echo $kml;
        }
    }
}
   
?>