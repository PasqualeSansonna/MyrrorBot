<?php

//Pagine utilizzata per ottenere il nome utente che sarà utilizzato nella grafica del sito

include "readLocaljson.php";

$mail = $_POST['mail'];
nominativo($mail);	

function nominativo($mail){

	$param = "";
	$json_data = queryMyrror($param,$mail);
	$result = null;

	foreach ($json_data as $key1 => $value1) {
	
		if(isset($value1['name'])){

			foreach ($value1['name'] as $key2 => $value2) {

				if ($key2 == "value") {
					$result = $value2;
				} 	
        	}	
		}
	}

	
	if (isset($result)) {
		$answer = $result;
	}else{
		$answer = "Utente";
	}

	echo $answer;
}
