<?php
# replace ****** below with unique google service account values
// https://developers.google.com/accounts/docs/OAuth2ServiceAccount
const CLIENT_ID = '****************.apps.googleusercontent.com';
const SERVICE_ACCOUNT_NAME = '****************@developer.gserviceaccount.com';    
    // Make sure you keep your key.p12 file in a secure location, and isn't
    // readable by others.
const KEY_FILE = '**********************-privatekey.p12';

function get_client() {
   
    
    // Load the key in PKCS 12 format (you need to download this from the
    // Google API Console when the service account was created.
    $client = new Google_Client();
    $key = file_get_contents(KEY_FILE);
    $client->setClientId(CLIENT_ID);
    $client->setAssertionCredentials(new Google_AssertionCredentials(
      SERVICE_ACCOUNT_NAME,
      array('https://www.googleapis.com/auth/fusiontables'),
      $key)
    );
    return $client;
}

?>