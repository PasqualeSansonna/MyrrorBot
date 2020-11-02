<?php 

require 'vendor/autoload.php';

/* Spotify Application Client ID and Secret Key */
function setTk(){ //TOKEN

$client_id     = 'a0f67602018a4d54a541ef1d203cefee'; 
$client_secret = '6472f3ca37484020b50fae253282d5f1'; 

$session = new SpotifyWebAPI\Session(
    $client_id,
    $client_secret
);

$session->requestCredentialsToken();
$accessToken = $session->getAccessToken();
 
return $accessToken;
// Store the access token somewhere. In a cookie for example.
//$cookie_name = "Spotifytoken";
//setcookie($cookie_name, $accessToken, time() + (86400 * 30), "/");//1 giorno

// Send the user along and fetch some data!
//header('Location: spotifyFetch.php');
//die();

}

