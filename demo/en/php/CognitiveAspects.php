<?php

//Permette di determinare i 5 tipi di personalità retivi ad un individuo
function personalita($resp,$parameters,$email){

	$param = "";
	$json_data = queryMyrror($param,$email);
	$result = null;

	$openness = "";
	$conscientiousness = "";
	$extroversion = "";
	$agreeableness = "";
	$neuroticism = "";
	$confidence = "";

	foreach ($json_data as $key1 => $value1) {

		if(isset($value1['personalities'])){

			$max = 0;

			foreach ($value1['personalities'] as $key2 => $value2) {

				$timestamp = $value2['timestamp'];

				$openness = $value2['openness'];
				$conscientiousness = $value2['conscientiousness'];
				$extroversion = $value2['extroversion'];
				$agreeableness = $value2['agreeableness'];
				$neuroticism = $value2['neuroticism'];
				$confidence = $value2['confidence'];
		 
         		if($timestamp > $max ){
         
           			$max = $timestamp;
           			$openness = $value2['openness'];
					$conscientiousness = $value2['conscientiousness'];
					$extroversion = $value2['extroversion'];
					$agreeableness = $value2['agreeableness'];
					$neuroticism = $value2['neuroticism'];
					$confidence = $value2['confidence'];
         		}	
        	}	
		}
	}


	//openness
	if ($openness > 0.5){
		$personalita1 = "inventive and curious";
	} else{
		$personalita1 = "concrete and cautious";
	}

	//conscientiousness
	if ($conscientiousness > 0.5){
		$personalita2 = "efficient and organized";
	} else{
		$personalita2 = "relaxed and negligent";
	}

	//extroversion
	if ($extroversion > 0.5){
		$personalita3 = "extrovert and energetic";
	} else{
		$personalita3 = "solitary and reserved";
	}

	//agreeableness 
	if ($agreeableness  > 0.5){
		$personalita4 = "friendly and compassionate";
	} else{
		$personalita4 = "aloof";
	}
	
	//neuroticism
	if ($neuroticism > 0.5){
		$personalita5 = "sensitive and nervous";
	} else{
		$personalita5 = "confident";
	}

	$answer = $resp . " " . $personalita1 . ", " . $personalita2 . ", " . $personalita3 . ".<br>Moreover, you are also " . $personalita4 . ", " . $personalita5;

	return $answer;

}


//Funzione che permette di fornire risposte binarie relative a domande sulla personalità
function personalitaBinario($resp,$parameters,$email){

    $answer = "I couldn't figure it out, try again in other words";
	$param = "";
	$json_data = queryMyrror($param,$email);
	$result = null;

	$openness = "";
	$conscientiousness = "";
	$extroversion = "";
	$agreeableness = "";
	$neuroticism = "";
	$confidence = "";

	//Prendo le personalità più recenti 
	foreach ($json_data as $key1 => $value1) {

		if(isset($value1['personalities'])){

			$max = 0;

			foreach ($value1['personalities'] as $key2 => $value2) {

				$timestamp = $value2['timestamp'];

				$openness = $value2['openness'];
				$conscientiousness = $value2['conscientiousness'];
				$extroversion = $value2['extroversion'];
				$agreeableness = $value2['agreeableness'];
				$neuroticism = $value2['neuroticism'];
				$confidence = $value2['confidence'];
		 
         		if($timestamp > $max ){
           			$max = $timestamp;
           			$openness = $value2['openness'];
					$conscientiousness = $value2['conscientiousness'];
					$extroversion = $value2['extroversion'];
					$agreeableness = $value2['agreeableness'];
					$neuroticism = $value2['neuroticism'];
					$confidence = $value2['confidence'];
         		}	
        	}	
		}
	}

	/*Prendo la entity dai parameters per capire a quale personalità mi riferisco ed effettuo i controlli
	Ad esempio se nella frase è presente la parola estroverso, verrà effettuato un controllo se effettivamente quella persona è estroversa
	*/
	if ($parameters['OpennessSi'] != "") {
		$entity = $parameters['OpennessSi'];
		
		if ($openness > 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you are concrete and cautious";
		}
		
	}else if ($parameters['OpennessNo'] != ""){
		$entity = $parameters['OpennessNo'];

		if ($openness <= 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you are creative and curious";
		}

	}else if ($parameters['ConscientiousnessSi'] != ""){
		$entity = $parameters['ConscientiousnessSi'];

		if ($conscientiousness > 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you are relaxed and negligent";
		}

	}else if ($parameters['ConscientiousnessNo'] != ""){
		$entity = $parameters['ConscientiousnessNo'];

		if ($conscientiousness <= 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you are efficient and organized";
		}
	}else if ($parameters['ExtroversionSi'] != ""){
		$entity = $parameters['ExtroversionSi'];

		if ($extroversion > 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you're lonely and reserved";
		}

	}else if ($parameters['ExtroversionNo'] != ""){
		$entity = $parameters['ExtroversionNo'];

		if ($extroversion <= 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you are extrovert and energetic";
		}
	}else if ($parameters['AgreeablenessSi'] != ""){
		$entity = $parameters['AgreeablenessSi'];

		if ($agreeableness > 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you're detached";
		}

	}else if ($parameters['AgreeablenessNo'] != ""){
		$entity = $parameters['AgreeablenessNo'];

		if ($agreeableness <= 0.5) {
			$answer = "Yes, you're" . $entity;
		}else{
			$answer = "No, you are friendly and compassionate";
		}
	}else if ($parameters['NeuroticismSi'] != ""){
		$entity = $parameters['NeuroticismSi'];

		if ($neuroticism > 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you are confident";
		}

	}else if ($parameters['NeuroticismNo'] != ""){
		$entity = $parameters['NeuroticismNo'];

		if ($neuroticism <= 0.5) {
			$answer = "Yes, you're " . $entity;
		}else{
			$answer = "No, you're sensitive and nervous";
		}
	}


	return $answer;

}