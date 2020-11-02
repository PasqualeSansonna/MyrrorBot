<?php
include "myrror/json_read.php";

function getLogData(){
    $param = "";
	$json_data = queryMyrror($param);
    $data = getMyrrorData($json_data);
    
    return $data;
}

function createURL($mood, $stress, $depression, $underweight, $overweight, $activity, $sleep, $vegetarian, $lactose, $gluten, $nickel, $light, $exp){
    $url = "http://localhost:5002/mood/?";
    
    if($mood == 'Bad')
        $url = $url . "mood=bad&";
    if($stress== 'yes')
        $url = $url . "stress=yes&";
    if($depression == 'yes')
        $url = $url . "depression=yes&";
    if($underweight)
        $url = $url . "underweight=yes&";
    if($overweight)
        $url = $url . "overweight=yes&";
    if($activity == "high")
        $url = $url . "activity=high&";
    if($activity == "low")
        $url = $url . "activity=low&";
    if($sleep == 'low')
        $url = $url . "sleep=low&";
    if($vegetarian)
        $url = $url . "isVegetarian=1&";
    if($lactose)
        $url = $url . "isLactoseFree=1&";
    if($gluten)
        $url = $url . "isGlutenFree=1&";
    if($nickel)
        $url = $url . "isLowNickel=1&";
    if($light)
        $url = $url . "'isLight'=1&";

    $url = $url . "difficulty=". $exp ."&";
    $url = $url . "n=5&lang=en";  
            
    return $url;
}


function getRecipes($pers_url){
    $pers_data_primo = performRequest(($pers_url."&category=Primi%20piatti"));
    $not_pers_data_primo = performRequest("http://localhost:5002/mood/?category=Primi%20piatti&n=10&lang=en");
    
    $pers_data_secondo = performRequest(($pers_url."&category=Secondi%20piatti"));
    $not_pers_data_secondo = performRequest("http://localhost:5002/mood/?category=Secondi%20piatti&n=10&lang=en");
    
    $pers_data_dolce = performRequest(($pers_url."&category=Dolci"));
    $not_pers_data_dolce = performRequest("http://localhost:5002/mood/?category=Dolci&n=10&lang=en");
    
    return array("personalized_main" => $pers_data_primo, "not_personalized_main" => $not_pers_data_primo, "personalized_second" => $pers_data_secondo, "not_personalized_second" => $not_pers_data_secondo, "personalized_dessert" => $pers_data_dolce, "not_personalized_dessert" => $not_pers_data_dolce);
}

function performRequest($url){
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
		$name = $arr["data"][0][1];
		$imageURL = $arr["data"][0][4];
		$ingredients = $arr["data"][0][24];
		$description = $arr["data"][0][5];
        $url = $arr["data"][0][0];
        
		
		return array("name" => $name, "imgURL" => $imageURL, "ingredients" => $ingredients, "description" => $description, "url" => $url);
	}
	else{
		return array("name" => "Purtroppo non ho trovato nessuna ricetta &#x1f60c");
	}
}

function getMyrrorData($json_data){
	$dateOfBirth = null;
    $height = null;
    $weight = null;
    $emotion = null;
    $mood = null;
    $stress = null;
    $BMI=null;
    $depression = null;
    $activity = array(0,0,0);
    $overweight = null;
    $underweight = null;    
    $count = 0;
    $sumInBed = 0;
    $sumAsleep = 0;
    $sleep = null;

	foreach ($json_data as $key1 => $value1) {
		//trovo la data di nascita
        if(isset($value1['dateOfBirth'])){
			foreach ($value1['dateOfBirth'] as $key2 => $value2) {
				if ($key2 == "value") {
					$dateOfBirth = $value2;
				} 	
        	}	
		}
        
        //trovo l'altezza
        if(isset($value1['height'])){
			foreach ($value1['height'] as $key2 => $value2) {
				if ($key2 == "value") {
					$height = $value2;
				} 	
        	}	
		}
        
        //trovo il peso
        if(isset($value1['weight'])){
			foreach ($value1['weight'] as $key2 => $value2) {
				if ($key2 == "value") {
					$weight = $value2;
				} 	
        	}	
		}
        
        //trovo l'umore
        $max = "";
        if($key1 == 'affects'){
			foreach ($value1 as $affect) {
                $date = substr($affect['date'],0, 10);
				if ($date > $max) {
					$emotion = $affect['emotion'];
                    $max = $date;
				} 	
        	}	
		}
        
        //controllo l'attività
        $max = 0;
        if(isset($value1['fromActivity'])){
            foreach ($value1['fromActivity'] as $key2 => $value2) {
                $timestamp = $value2['timestamp'];
                if($timestamp >= $max && $value2['nameActivity'] != "calories"  && $value2['nameActivity'] != "steps" && $value2['nameActivity'] != "minutesSedentary"  && $value2['nameActivity'] != "distance"){
                    switch ($value2['nameActivity']) {
                        case 'fairly':
                            $activity[0] = $value2['minutesFairlyActive'];        
                            $max = $timestamp;
                            break;
                        
                        case 'minutesLightlyActive':
                            $activity[1] = $value2['minutesLightlyActive'];
                            $max = $timestamp;
                            break;
                        
                        case 'veryActive':
                            $activity[2] = $value2['minutesVeryActive'];
                            $max = $timestamp;
                            break;
                        
                        default:
                            break;
                    }
                }
            }
        }
        
        //sonno
        if(isset($value1['sleep'])){
            //ricerca per periodo   
            foreach ($value1['sleep'] as $key2 => $value2) {
                $sumInBed += $value2['timeInBed'];
                $sumAsleep += $value2['minutesAsleep'];
                $count++;         
            }
        }
        
	}
    
    //calcolo età
    $years = null;
	if(!is_null($dateOfBirth)){
		$today = date("Y-m-d");
		$diff = abs(strtotime($today) - strtotime($dateOfBirth));
    	$years = floor($diff / (365*60*60*24));
	}
        
    //controllo sull'emozione per avvalorare mood, depression e stress
	switch ($emotion) { //gioia, paura, rabbia, disgusto, tristezza, sorpresa
        case "fear\n":
            $mood = "Bad";
            $stress = "no"; 
            $depression = "no";
        break;
      case "anger\n":
            $mood = "Bad";
			$stress = "yes"; 
            $depression = "no";
        break;
      case "disgust\n":
			$mood = "Bad";
            $stress = "no"; 
            $depression = "no";
        break;
      case "sad\n":
            $mood = "Bad";
            $stress = "no"; 
			$depression = "yes";
        break;
    }
    
    //regola per l'attività fisica
    $sum = $activity[0] + $activity[1] + $activity[2];
	if($sum >=30){
		$activity = "high";
	}
	else{
		$activity = "low";
	}
    
    //controllo sul sonno
    if($count != 0){
        $sleepAV = intval($sumAsleep/$count);
        $inBedAV =intval($sumInBed/$count);
        
        if($sleepAV <= 390)
            $sleep = "low";
        else $sleep = "high";
    }
    
    //calcoloBMI
    if(!is_null($height) && !is_null($weight)){
         $BMI = $weight / (pow(($height/100),2));
    }
    
    if(!is_null($BMI) && $BMI > 25){
        $overweight = true;
        $underweight = false;
    }
    else if(!is_null($BMI) && $BMI < 18.5){
        $overweight = false;
        $underweight = true;
    }
    else if(!is_null($BMI)){
        $overweight = false;
        $underweight = false;
    }

    
    return array("age" => $years,"overweight" => $overweight, "underweight" => $underweight, "mood" => $mood, "stress" => $stress, "depression" => $depression, "activity" => $activity, "sleep" => $sleep);
}

function createIngText($ingredients){
    $ingredients = str_replace("\"","",$ingredients);
    $ingredients = str_replace("[","",$ingredients);
    $ingredients = str_replace("]","",$ingredients);
    $ingredients = str_replace(",","<br>",$ingredients);
    return $ingredients;
}