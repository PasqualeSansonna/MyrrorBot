<?php 

	include "myrrorlogin.php";

	$email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $token  = trim($_POST['token']);

    //Salvo il token nei cookie
    $cookie_name = "myrror";
	$cookie_email = $email;
	setcookie($cookie_name, $cookie_email, time() + (86400 * 30), "/"); // 86400 = 1 day

    $credenziali = "email=" . $email . "&password=" . $password;

	$response = queryMyrror("", $credenziali);
	$fp = fopen('../fileMyrror/past_'. $email . ".json", 'w+');
	fwrite($fp, json_encode($response));
	fclose($fp);

	$today = date('Y-m-d');
	$response = queryMyrror("?fromDate=".$today,  $credenziali);
	$fp = fopen('../fileMyrror/today_'. $email . ".json", 'w+');
	fwrite($fp, json_encode($response));
	fclose($fp);

	echo "ok";	//Risposta per identificare l'avvenuta creazione dei file



?> 