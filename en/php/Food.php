<?php
/*
Parameters contiene gli argomenti restituiti da dialogflow
Tramite parameters controlliamo quale sia il parametro individuato
da dialogflow e costruiamo di conseguenza il link di ricerca in 
base alla categoria.
*/

require_once  'Affects.php'; //Per le emozioni
require_once  'Demographics.php'; //Per l'età
require_once  'Behaviors.php'; //Per i dati sull'attività fisica
require_once  'PhysicalState.php'; //Per i dati sul sonno

function getRecipe($resp,$parameters,$text,$email){
    $text = str_replace("?", "", $text);
    
	$listaParoleRaccomandazioni = array('tell me', 'recommen', 'what can i', 'suggest');//raccomandazioni
    $listaParoleIngredient = array(' of ', ' containing ', ' with the ', ' with ');
	$listaHealthy = array('healthy');
    $listaLight = array('light'); 
    $listaVeg = array('vegetarian');
    $listaLac = array('lactose', 'lactose-free');
    $listaNick = array('nickel');
    $listaGluten = array('gluten', 'gluten-free');

	
	$flagRaccomandazioni = false;
	$flagIngredienti = false;
	$flagHealthy = false;
    $flagLight = false;
    $ingredient = "";
    $flagVeg = false;
    $flagLac = false;
    $flagNick = false;
    $flagGluten = false;    


	//echo $text . "\n\n";
	
	//Controllo se sono presenti le parole delle raccomandazioni allora vado nella sezione delle Ricette RACCOMANDATE
	foreach($listaParoleRaccomandazioni as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagRaccomandazioni = true;
   			break;
		} 
   	}
    
    //Controllo se sono presenti le parole della lista ingredienti allora setto a vero il flag ingredienti
	foreach($listaParoleIngredient as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagIngredienti = true;
            $ingredient = explode($parola, $text)[1];
   			break;
		} 
   	}
	
	//Controllo se sono presenti le parole della lista healty allora setto a vero il flag healty
	foreach($listaHealthy as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagHealthy = true;
   			break;
		} 
   	}
    
    //Controllo se sono presenti le parole della lista light allora setto a vero il flag light
	foreach($listaLight as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagLight = true;
   			break;
		} 
   	}
    
    //Controllo se sono presenti le parole della lista veg allora setto a vero il flag veg
	foreach($listaVeg as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagVeg = true;
   			break;
		} 
   	}
    
    //Controllo se sono presenti le parole della lista lac allora setto a vero il flag lac
	foreach($listaLac as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagLac = true;
   			break;
		} 
   	}
    
    //Controllo se sono presenti le parole della lista lac allora setto a vero il flag lac
	foreach($listaNick as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagNick = true;
   			break;
		} 
   	}
    
    //Controllo se sono presenti le parole della lista gluten allora setto a vero il flag gluten
	foreach($listaGluten as $parola)  {  
   		if (stripos($text, $parola) !== false) {
    		//Contiene la parola
   			$flagGluten = true;
   			break;
		} 
   	}
	
		
	
	if($flagIngredienti && $flagRaccomandazioni){
		$answer = getPersonalizedRecipe($resp,$parameters,$text,$email, TRUE, $ingredient, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
	}
	else if($flagIngredienti){
		$answer = getRecipeByIngredient($resp,$parameters,$text,$email, $ingredient, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
	}
	else if($flagRaccomandazioni){
		$answer = getPersonalizedRecipe($resp,$parameters,$text,$email, FALSE, $ingredient, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
	}
	else if ($parameters['FoodType'] != "") {
	   	$answer = getRecipeByType($resp,$parameters,$text,$email, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
 	}
    else{
        $answer = getStandardRecipe($resp,$parameters,$text,$email, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
    }
	
	return $answer;
}

function getStandardRecipe($resp,$parameters,$text,$email, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten){
    $url = "http://localhost:5002/mood/?n=100";
    
    if($flagHealthy){
        $url = $url . "&healthy=high";
    }
    
    if($flagLight){
        $url = $url . "&isLight=1";
    }
    
    if($flagVeg){
        $url = $url . "&isVegetarian=1";
    }
    
    if($flagLac){
        $url = $url . "&isLactoseFree=1";
    }
    
    if($flagNick){
        $url = $url . "&isLowNickel=1";
    }        
    
    if($flagGluten){
        $url = $url . "&isGlutenFree=1";
    }        
    
	$spiegazione = "I suggested this recipe since it is very popular &#x1F958";
        
	return performRequest($url,$spiegazione);	
}

/*Crea un url per la richesta al WS contenente la categoria della ricetta (Es:primi, secondi ecc)*/
function getRecipeByType($resp,$parameters,$text,$email, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten){
	//salvo il tipo di richiesta e preparo l'url 
	$type = $parameters['FoodType'];
	$type = str_replace(" ", "%20", "$type");
	$url = "http://localhost:5002/mood/?category=" . $type . "&n=100&lang=en";
    
    if($flagHealthy){
        $url = $url . "&healthy=high";
    }
    
    if($flagLight){
        $url = $url . "&isLight=1";
    }
          
    if($flagVeg){
        $url = $url . "&isVegetarian=1";
    }
    
    if($flagLac){
        $url = $url . "&isLactoseFree=1";
    }
    
    if($flagNick){
        $url = $url . "&isLowNickel=1";
    }
    if($flagGluten){
        $url = $url . "&isGlutenFree=1";
    }   
	$spiegazione = "I suggested this recipe since it is very popular &#x1F958";
    
	return performRequest($url,$spiegazione);	
}

/*Crea un url per la richesta al WS contenente un ingrediente specifico
*/
function getRecipeByIngredient($resp,$parameters,$text,$email, $ingredient, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten){
	$type = $parameters['FoodType'];
	
	$url = "http://localhost:5002/mood/?";
	
	if($type != ""){
		$type = str_replace(" ", "%20", "$type");
		$url = $url . "category=" .$type . "&";
	}
	
	
	$url = $url . "ingredient=" . $ingredient . "&n=100&lang=en";
    
    $spiegazione = "I suggested this recipe since it has all the requested ingredient";
    
    if($flagHealthy){
        $url = $url . "&healthy=high";
        $spiegazione = $spiegazione . " and it is healthy";
    }
    
    if($flagLight){
        $url = $url . "&isLight=1";
        $spiegazione = $spiegazione . " and it is light";
    }
    
    if($flagVeg){
        $url = $url . "&isVegetarian=1";
        $spiegazione = $spiegazione . " and it is vegetarian";
    }
    
    if($flagLac){
        $url = $url . "&isLactoseFree=1";
        $spiegazione = $spiegazione . " and it is lactose-free";
    }
    
    if($flagNick){
        $url = $url . "&isLowNickel=1";
        $spiegazione = $spiegazione . " and it has low nickel";
    }
    
    if($flagGluten){
        $url = $url . "&isGlutenFree=1";
        $spiegazione = $spiegazione . " and it is gluten-free";
    }   
	    
	$spiegazione = $spiegazione . " &#x1F958";
    
	return performRequest($url,$spiegazione);	
}

/*Crea un url per la richesta al WS contente i parametri calcolati in base allo stato dell'utente*/
function getPersonalizedRecipe($resp,$parameters,$text,$email, $flagIngredient, $ingredient, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten){
	
	$emotion = getLastEmotion($email); //Rilevo l'ultima emozione dell'utente
	$weight = getWeight($resp,$parameters,$text,$email);
	$heigh = getHeight($resp,$parameters,$text,$email);
	$sleep = getSleepBinary($resp,$parameters,$text,$email);
	$sum = 0;
	$type = $parameters['FoodType'];
	
	$mood = "";
	$activity = "";
	$stress = false;
	$depression = false;
	$underweight = false;
	$overweight = false;
	$evening = false;
	
	
	
	if ($email == '') {
		return '';
	}

	//controllo sull'emozione per avvalorare mood, depression e stress
	switch ($emotion) { //gioia, paura, rabbia, disgusto, tristezza, sorpresa
      case 'paura':
			$mood = "bad";
        break;
      case 'rabbia':
			$stress = true;        
        break;
      case 'disgusto':
			$mood = "bad";
        break;
      case 'tristezza':
			$depression = "true";
        break;
    }
	
	//controllo sul sonno "No, non dormi abbastanza. In media dormi"
	if(stripos($sleep, "No,") !== false){
		$sleep = "low";
	}
	else{
		$sleep = "";
	}
	
	//calcolo BMI per capire se l'utente è sovrappeso/sottopeso
	if (is_numeric($weight) && is_numeric($heigh)){
		$BMI = $weight / (pow(($heigh/100),2));
		
		//echo $BMI;
		if($BMI > 25)
			$overweight = true;
		else if($BMI < 18.5)
			$underweight = true;
	}
	
    $spiegazione = "I suggested this recipe since ";
    $url = "http://localhost:5002/mood/?";

    
	//attività fisica
	$date = date("Y/m/d");
	$activity = attivitaData($date,$email);
	$sum = $activity[0] + $activity[1] + $activity[2];
	if($sum >=30){
		$activity = "high";
	}
	else{
		$activity = "low";
	}
	
	//controllo se è pomeriggio
	if((date("H") + 2) >18){
		$hour = "evening";
	}
	
	if($mood == 'bad'){
		$url = $url . "mood=bad&";
        
        $spiegazione = $spiegazione . " your mood is bad,";
	}
	
	if($stress){
		$url = $url . "stress=yes&";
        
        $spiegazione = $spiegazione . " you're stressed,";
	}
	
	if($depression){
		$url = $url . "depression=yes&";
        
        $spiegazione = $spiegazione . " you're depressed,";
	}
	
	if($underweight){
		$url = $url . "underweight=yes&";
        
        $spiegazione = $spiegazione . " you're underweight,";
	}
	
	if($overweight){
		$url = $url . "overweight=yes&";
        
        $spiegazione = $spiegazione . " you're overweight,";
	}
	
	if($evening){
		$url = $url . "hour=evening&";
	}
	
	if($activity == "high"){
		$url = $url . "activity=high&";
        
        $spiegazione = $spiegazione . " you do enough physical activity &#x1F958";
	}

	if($activity == "low"){
		$url = $url . "activity=low&";
        
        $spiegazione = $spiegazione . " you don't do enough physical activity &#x1F958";
	}
	
	if($type != ""){
		$type = str_replace(" ", "%20", "$type");
		$url = $url . "category=" .$type . "&";
	}
	
	
	if($flagIngredient){
		$url = $url . "ingredient=" . $ingredient . "&";
	}
	
	$url = $url . "n=10&lang=en";
    
    if($flagHealthy){
        $url = $url . "&healthy=high";
    }
    
    if($flagLight){
        $url = $url . "&isLight=1";
    }
    
    if($flagVeg){
        $url = $url . "&isVegetarian=1";
    }
    
    if($flagLac){
        $url = $url . "&isLactoseFree=1";
    }
    
    if($flagNick){
        $url = $url . "&isLowNickel=1";
    }
    
     if($flagGluten){
        $url = $url . "&isGlutenFree=1";
    }     
    
    $spiegazione = strrev(implode(strrev(' and '), explode(strrev(','), strrev($spiegazione), 2)));
        
	return performRequest($url,$spiegazione);	
}

/*esegue la richiesta al webservice e processa il risultato in modo da restituire un json 
contentente "name" -> nome della ricetta,"imgURL" -> url dell'immagine,
"ingredients" -> lista degli ingredienti, "url" -> url dela ricetta su gialloZafferano, 
"description" -> descriizione della ricetta,"procedure" -> procedimento per la preparazione
*/
function performRequest($url, $spiegazione){
	//  Initiate curl
	$ch = curl_init();
	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// Set the url
	curl_setopt($ch, CURLOPT_URL, $url);
	// Execute
	$result=curl_exec($ch);
	// Closing
	curl_close($ch);

	//sostituisco \" con " e \\ con \ nel risultato, poi elimino il primo carattere (") e gli ultimi due ("\n)
	//$result = str_replace("\\", "", "$result");
	$result = str_replace('\\"', "\"", "$result");
	$result = str_replace('\\\\', "\\", "$result");
	$result = substr($result, 1);
	$result = substr_replace($result ,"", -2);	
	//echo $result;
	
	//vado a decodificare il json e lo metto in un array
	$arr = json_decode($result,true);	
	
	//salvo il nome della ricetta, l'url dell'immagine e gli altri elementi da restituire
	if(!empty($arr["data"])) {
		/*$name = $arr["data"][0][1];
		$imageURL = $arr["data"][0][4];
		$ingredients = $arr["data"][0][24];
		$description = $arr["data"][0][5];
		$procedure = $arr["data"][0][26];
		$url = $arr["data"][0][0];
		
		return array("name" => $name, "imgURL" => $imageURL, "ingredients" => $ingredients, "url" => $url, "description" => $description,
					"procedure" => $procedure, "explain" => $spiegazione);*/
        return array("recipes" => $arr["data"], "explain" => $spiegazione); 
	}
	else{
        return array("name" => "Sadly i can't find any useful recipe &#x1f60c");
	}
}
?>