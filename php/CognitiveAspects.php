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
		$personalita1 = "inventivo e curioso";
	} else{
		$personalita1 = "concreto e cauto";
	}

	//conscientiousness
	if ($conscientiousness > 0.5){
		$personalita2 = "efficiente ed organizzato";
	} else{
		$personalita2 = "rilassato e negligente";
	}

	//extroversion
	if ($extroversion > 0.5){
		$personalita3 = "estroverso ed energico";
	} else{
		$personalita3 = "solitario e riservato";
	}

	//agreeableness 
	if ($agreeableness  > 0.5){
		$personalita4 = "amichevole e compassionevole";
	} else{
		$personalita4 = "distaccato";
	}
	
	//neuroticism
	if ($neuroticism > 0.5){
		$personalita5 = "sensibile e nervoso";
	} else{
		$personalita5 = "fiducioso";
	}

	$answer = $resp . " " . $personalita1 . ", " . $personalita2 . ", " . $personalita3 . ".<br>Inoltre sei anche " . $personalita4 . ", " . $personalita5;

	return $answer;

}


//Funzione che permette di fornire risposte binarie relative a domande sulla personalità
function personalitaBinario($resp,$parameters,$email){

    $answer = "non sono riuscito a stabilirlo, riprova con altre parole";
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
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei concreto e cauto";
		}
		
	}else if ($parameters['OpennessNo'] != ""){
		$entity = $parameters['OpennessNo'];

		if ($openness <= 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei creativo e curioso";
		}

	}else if ($parameters['ConscientiousnessSi'] != ""){
		$entity = $parameters['ConscientiousnessSi'];

		if ($conscientiousness > 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei rilassato e negligente";
		}

	}else if ($parameters['ConscientiousnessNo'] != ""){
		$entity = $parameters['ConscientiousnessNo'];

		if ($conscientiousness <= 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei efficiente ed organizzato";
		}
	}else if ($parameters['ExtroversionSi'] != ""){
		$entity = $parameters['ExtroversionSi'];

		if ($extroversion > 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei solitario e riservato";
		}

	}else if ($parameters['ExtroversionNo'] != ""){
		$entity = $parameters['ExtroversionNo'];

		if ($extroversion <= 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei estroverso ed energico";
		}
	}else if ($parameters['AgreeablenessSi'] != ""){
		$entity = $parameters['AgreeablenessSi'];

		if ($agreeableness > 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei distaccato";
		}

	}else if ($parameters['AgreeablenessNo'] != ""){
		$entity = $parameters['AgreeablenessNo'];

		if ($agreeableness <= 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei amichevole e compassionevole";
		}
	}else if ($parameters['NeuroticismSi'] != ""){
		$entity = $parameters['NeuroticismSi'];

		if ($neuroticism > 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei fiducioso";
		}

	}else if ($parameters['NeuroticismNo'] != ""){
		$entity = $parameters['NeuroticismNo'];

		if ($neuroticism <= 0.5) {
			$answer = "Si, sei " . $entity;
		}else{
			$answer = "No, sei sensibile e nervoso";
		}
	}


	return $answer;

}