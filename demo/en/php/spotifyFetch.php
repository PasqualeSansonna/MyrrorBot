<?php

require 'vendor/autoload.php';

function getApi(){

    $accessToken = setTk();//Token
	/* Fetch the saved access token from somewhere. A cookie for example.
	if(!isset($_COOKIE["Spotifytoken"])) {
	    echo "Access Token is not set!";
	} else {
	    $accessToken = $_COOKIE["Spotifytoken"];
	}
*/
	$api = new SpotifyWebAPI\SpotifyWebAPI();
	$api->setAccessToken($accessToken);

	return $api;
}
