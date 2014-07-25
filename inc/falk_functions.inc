<?php

function curl_request($sql, $table_key) {
    $fusion_table_url = "https://www.googleapis.com/fusiontables/v1/query";
    $api_key = "***********************************";
    $arrHeader = array('Content-Type'=>'application/json');
    $sql_formatted = sprintf($sql, $table_key);
    $curl_url = sprintf($fusion_table_url); #, $sql_formatted, $api_key));
    $postfields = "sql=".$sql_formatted."&key=".$api_key;
    # print $curl_url;
    $ch = curl_init($curl_url);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $arrHeader);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt( $ch, CURLOPT_POST, 2);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfields);
    $response = curl_exec($ch);
    echo $response;
    return json_decode($response);
}

function get_token()
{
    if (isset($_SESSION["token"]))
    {
        $token = $_SESSION["token"];
        //echo $token;
    }
    else
    {
        $token = GoogleClientLogin("stasr25", "Save2schools", "fusiontables");
        $_SESSION["token"] = $token;
    }
    return $token;
}
?>