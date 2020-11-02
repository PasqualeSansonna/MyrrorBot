<?php


function queryMyrror($param,$email){

	$json = null;

	$email = 'demo@demo.it';

	if (isset($_COOKIE['myrror'])) {

	  	$email = $_COOKIE['myrror'];
    }
     	if($param == "today"){
			$json = file_get_contents('../fileMyrror/today_' . $email . '.json');
		}else{
			$json = file_get_contents('../fileMyrror/past_' . $email . '.json');
		}
		$result = json_decode($json,true);

		return $result;
/*
	}else{
		echo "<script>location.href='index.html';</script>";

	}
*/
	

}


?>