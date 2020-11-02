<?php

include 'readLocaljson.php';

$email = "cat@cat.it";

if(isset($_POST{'mail'})){
    $email = $_POST{'mail'};
    
}
$timestamp = 0;
$image = "";

$json_data = queryMyrror('past',$email);

foreach ($json_data['demographics']['image'] as $key => $value) {
	
	if ($value['source']  == "instagram"  && $value['timestamp'] > $timestamp && $value['value'] != null){
		$timestamp = $value['timestamp'];
		$image = $value['value'];
	}
	
}

print($image);





?>