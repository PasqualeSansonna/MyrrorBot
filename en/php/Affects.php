<?php

/*Permette di fornire dati in relazione alla domanda richiesta
Viene fornito un flag per determinare se ci si riferisce alle emozioni oppure alla personalità
*/ 
function getSentiment($flag, $resp, $parameters,$email){

    //flag 1 --> emozioni
    //flag 0 --> l'umore

    if($flag == 1){ //EMOZIONI (Fear, sad, anger, joy, disgust, surprise, none)

      if ($parameters['date'] != "") { //La data inserita dall'utente è stata riconosciuta
        
        //DATA RICHIESTA DALL'UTENTE
        $dataR = substr($parameters['date'], 0, 10);
        $data = str_replace('-', '/', $dataR);

        //OGGI
        $oggi = date("Y/m/d");

        //IERI
        $date1 = str_replace('-', '/', date("Y/m/d"));
        $ieri = date('Y/m/d',strtotime($date1 . "-1 days"));

        //Controllo se la data si riferisce a ieri/oggi
        if ($data == $ieri) {
          $answer = getPast($ieri,$email);
      
        }elseif ($data == $oggi) {
          $answer = getToday($oggi,$email);
        }

    
      }else{//DATA NON RICONOSCIUTA --> imposto "oggi" come default
        
        //OGGI
        $oggi = date("Y/m/d");
        $answer = getToday($oggi,$email);
      }

  
    }else{ //UMORE (negative, neuter, positive)

       if ($parameters['date'] != "") { //La data inserita dall'utente è stata riconosciuta
        
        //DATA RICHIESTA DALL'UTENTE
        $dataR = substr($parameters['date'], 0, 10);
        $data = str_replace('-', '/', $dataR);

        //OGGI
        $oggi = date("Y/m/d");

        //IERI
        $date1 = str_replace('-', '/', date("Y/m/d"));
        $ieri = date('Y/m/d',strtotime($date1 . "-1 days"));

        //Controllo se la data si riferisce a ieri/oggi
        if ($data == $ieri) {
          $answer = getPastUmore($ieri,$email);
      
        }elseif ($data == $oggi) {
          $answer = getTodayUmore($oggi,$email);
        }

    
      }else{//DATA NON RICONOSCIUTA --> imposto "oggi" come default
        
        //OGGI
        $oggi = date("Y/m/d");
        $answer = getTodayUmore($oggi,$email);
      }
    }

    return $answer;

}


//OGGI: determina l'umore in relazione ad oggi
function getTodayUmore($oggi,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
      
    $dataR = substr($value1['date'],0, 10);
    $data = str_replace('-', '/', $dataR);


    if($data == $oggi){
      $result = $value1;
    }
  }

  if(isset($result['sentiment'])){

    $mood = $result['sentiment'];

    if($mood == 1){
      $answer = "You are in a good mood";
    }else if($mood == -1){
      $answer = "You are in a bad mood";
    }else{
      $answer = "Your mood is neutral";
    }

  }else{ //Se non sono presenti dati relativi ad oggi

      $param = "past";
      $json_data = queryMyrror($param,$email);
      $result = null;
      $max = "";
      $emotion = "";

    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $mood = $result['sentiment'];

    if($mood == 1){
      $response = "you were in a good mood";
    }else if($mood == -1){
      $response = "you were in a bad mood";
    }else{
      $response = "your mood was neutral";
    }
    
    $answer = "Based on the latest data collected " . $response;
  }

  return $answer;
}


//IERI: determina l'umore in relazione a ieri
function getPastUmore($ieri,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
      
    $dataR = substr($value1['date'],0, 10);
    $data = str_replace('-', '/', $dataR);


    if($data == $ieri){
      $result = $value1;
    }
  }

  if(isset($result['sentiment'])){

    $mood = $result['sentiment'];

    if($mood == 1){
      $answer = "You were in a good mood";
    }else if($mood == -1){
      $answer = "You were in a bad mood";
    }else{
      $answer = "Your mood was neutral";
    }

  }else{ //Se non sono presenti dati relativi a ieri

      $param = "past";
      $json_data = queryMyrror($param,$email);
      $result = null;
      $max = "";
      $emotion = "";

    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $mood = $result['sentiment'];

    if($mood == 1){
      $answer = "You were in a good mood";
    }else if($mood == -1){
      $answer = "You were in a bad mood";
    }else{
      $answer = "Your mood was neutral";
    }
    
   $answer = "Based on the latest data collected " . $response;
  }

  return $answer;

}


//IERI: determina l'emozione in relazione ad oggi
function getPast($ieri,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
      
    $dataR = substr($value1['date'],0, 10);
    $data = str_replace('-', '/', $dataR);

    if($data == $ieri){
      $result = $value1;
    }
  }

  if(isset($result['emotion'] )){

    $emotion = getEmotion($result,$email);
    $answer =  "You were feeling " . $emotion ;

  }else{ //Se non sono presenti dati relativi a ieri
    
    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $emotion = getEmotion($result,$email);
    $answer = "Based on the latest data you were feeling " . $emotion;

  }

  return $answer;

}

//OGGI: determina l'emozione in relazione ad oggi
function getToday($oggi,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
      
    $dataR = substr($value1['date'],0, 10);
    $data = str_replace('-', '/', $dataR);

    if($data == $oggi){
      $result = $value1;
    }
  }

  if(isset($result['emotion'] )){

    $emotion = getEmotion($result,$email);

      switch (rand(1,2)) {
      case '1':
        $answer = "You were feeling " . $result;
        break;
      case '2':
        $answer = "In this moment you feel " . $result;
        break;
    }

  }else{ //Se non sono presenti dati relativi ad oggi

      $param = "past";
      $json_data = queryMyrror($param,$email);
      $result = null;
      $max = "";
      $emotion = "";

    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $emotion = getEmotion($result,$email);
   $answer = "Based on the latest data you were feeling " . $emotion;
  }

  return $answer;

}

//EMOZIONE: ritorna l'emozione corrispondente
function getEmotion($result,$email){

    if (strpos($result['emotion'], 'joy') !== false) {
      $emotion = "gioia";
    }else if (strpos($result['emotion'], 'fear') !== false) {
       $emotion = "paura";
    }else if (strpos($result['emotion'], 'anger') !== false) {
      $emotion = "rabbia";
    }else if (strpos($result['emotion'], 'disgust') !== false) {
      $emotion = "disgusto";
    }else if (strpos($result['emotion'], 'sad') !== false) {
      $emotion = "tristezza";
    }else if (strpos($result['emotion'], 'surprise') !== false) {
      $emotion = "sorpresa";
    }else{
         return "in this moment you don't feel anything";
    }

  return $emotion;

}


//Funzione utilizzata per gestire le risposte binarie
function getSentimentBinario($flag, $resp, $parameters,$email){

    //flag 1 --> emozioni
    //flag 0 --> l'umore

    if($flag == 1){ //EMOZIONI (Fear, sad, anger, joy, disgust, surprise, none)

      if ($parameters['date'] != "") { //La data inserita dall'utente è stata riconosciuta
        
        //DATA RICHIESTA DALL'UTENTE
        $dataR = substr($parameters['date'], 0, 10);
        $data = str_replace('-', '/', $dataR);

        //OGGI
        $oggi = date("Y/m/d");

        //IERI
        $date1 = str_replace('-', '/', date("Y/m/d"));
        $ieri = date('Y/m/d',strtotime($date1 . "-1 days"));

        //Controllo se la data si riferisce a ieri/oggi
        if ($data == $ieri) {
          $answer = getPastBinario($ieri, $parameters,$email);
      
        }elseif ($data == $oggi) {
          $answer = getTodayBinario($oggi, $parameters,$email);
        }

    
      }else{//DATA NON RICONOSCIUTA --> imposto "oggi" come default
        
        //OGGI
        $oggi = date("Y/m/d");

        $answer = getTodayBinario($oggi, $parameters,$email);
      }

  
    }else{ //UMORE (negative, neuter, positive)

      if ($parameters['date'] != "") { //La data inserita dall'utente è stata riconosciuta
        
        //DATA RICHIESTA DALL'UTENTE
        $dataR = substr($parameters['date'], 0, 10);
        $data = str_replace('-', '/', $dataR);

        //OGGI
        $oggi = date("Y/m/d");

        //IERI
        $date1 = str_replace('-', '/', date("Y/m/d"));
        $ieri = date('Y/m/d',strtotime($date1 . "-1 days"));

        //Controllo se la data si riferisce a ieri/oggi
        if ($data == $ieri) {
          $answer = getPastUmoreBinario($ieri, $parameters,$email);
      
        }elseif ($data == $oggi) {
          $answer = getTodayUmoreBinario($oggi, $parameters,$email);
        }

    
      }else{//DATA NON RICONOSCIUTA --> imposto "oggi" come default
        
        //OGGI
        $oggi = date("Y/m/d");

        $answer = getTodayUmoreBinario($oggi, $parameters,$email);
      }
    }

    return $answer;

}


//IERI: determina l'umore in relazione a ieri per le domande con risposta binaria
function getPastUmoreBinario($ieri, $parameters,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
      
    $dataR = substr($value1['date'],0, 10);
    $data = str_replace('-', '/', $dataR);

    if($data == $ieri){
      $result = $value1;
    }
  }

  if(isset($result['sentiment'] )){

     $mood = $result['sentiment'];

     if ($mood == 1 && $parameters['UmoreBuono'] != "") {
        $answer = "Yes, you were in a good mood";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $answer = "No, your mood was bad";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $answer = "No, your mood was neutral";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $answer = "Yes, you were in a terrible mood";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, your mood was positive";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, your mood was neutral";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $answer = "Yes, you had a neutral mood";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, your mood was positive";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, your mood was negative";
     }

  }else{ //Se non sono presenti dati relativi a ieri
    
    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $mood = $result['sentiment'];

     if ($mood == 1 && $parameters['UmoreBuono'] != "") {
        $risposta = "you were in a good mood";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $risposta = "your mood was bad";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $risposta = "your mood was neutral";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $risposta = "you were in a terrible mood";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $risposta = "il tuo umore era positivo";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $risposta = "your mood was neutral";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $risposta = "you had a neutral mood";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "your mood was positive";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "your mood was negative";
     }

       $answer = "Based on the latest data collected " . $risposta;

  }

  return $answer;

}


//OGGI: determina l'umore in relazione ad oggi per le domande con risposta binaria
function getTodayUmoreBinario($oggi, $parameters,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
    $dataR = substr($value1['date'], 0, 10);
    $data = str_replace('-', '/', $dataR);

    if($data == $oggi){
      $result = $value1;
    }
  }


  if(isset($result['sentiment'] )){
    $mood = $result['sentiment'];

     if ($mood == 1 && $parameters['UmoreBuono'] != "") {
        $answer = "Yes, you are in a good mood";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $answer = "No, your mood is bad";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $answer = "No, your mood is neutral";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $answer = "Yes, you're in a bad mood";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, your mood is positive";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, your mood is neutral";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $answer = "Yes, you have a neutral mood";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, your mood is positive";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, your mood is negative";
     }
      
  }else{ //Se non sono presenti dati relativi ad oggi

      $param = "past";
      $json_data = queryMyrror($param,$email);
      $result = null;
      $max = "";
      $emotion = "";

    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $mood = $result['sentiment'];

     if ($mood == 1 && $parameters['UmoreBuono'] != "") {
        $risposta = "you were in a good mood";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $risposta = "your mood was bad";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $risposta = "your mood was neutral";
     }
	 else $risposta = "your mood is neutral";

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $risposta = "you were in a terrible mood";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $risposta = "your mood was positive";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $risposta = "your mood was neutral";
     }
	else $risposta = "your mood is neutral";
	
      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $risposta = "you had a neutral mood";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "your mood is positive";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "your mood is bad";
     }
	else $risposta = "your mood is neutral";

        $answer = "Based on the latest data collected " . $risposta;
  }

  return $answer;

}

   
//IERI: determina l'emozione in relazione a ieri per le domande con risposta binaria
function getPastBinario($ieri, $parameters,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
      
    $dataR = substr($value1['date'],0, 10);
    $data = str_replace('-', '/', $dataR);

    if($data == $ieri){
      $result = $value1;
    }
  }

  if(isset($result['emotion'] )){

    $emotion = getEmotion($result,$email);

    switch ($emotion) {
      case 'gioia':
        if ($parameters['EmotionJoy'] != "") {
          $entity = $parameters['EmotionJoy'];
          $answer = "Yes, you were " . $entity;
        }else{
          $answer = "No, you were happy";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $answer = "Yes, you were " . $entity;
        }else{
          $answer = "No, you were scared";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $answer = "Yes, you were " . $entity;
        }else{
          $answer = "No, you were angry";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $answer = "Yes, you were " . $entity;
        }else{
          $answer = "No, you were disgusted";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $answer = "Yes, you were " . $entity;
        }else{
          $answer = "No, you were sad";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $answer = "Yes, you were " . $entity;
        }else{
          $answer = "No, you were surprised";
        }
        break;
      default:
          $answer = "No, you weren't feeling any emotion";
        break;
    }

  }else{ //Se non sono presenti dati relativi a ieri
    
    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $emotion = getEmotion($result,$email);


    switch ($emotion) {
      case 'gioia':
        if ($parameters['EmotionJoy'] != "") {
          $entity = $parameters['EmotionJoy'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were happy";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were scared";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $risposta = "Yes, you were" . $entity;
        }else{
          $risposta = "No, you were angry";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were disgusted";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were sad";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were surprised";
        }
        break;
      default:
          $risposta = "No, you weren't feeling any emotion";
        break;
    }

    $answer = "Based on the latest data <br>" . $risposta;

  }

  return $answer;

}

//OGGI: determina l'emozione in relazione ad oggi per le domande con risposta binaria
function getTodayBinario($oggi, $parameters,$email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  foreach ($json_data['affects'] as $key1 => $value1) {
    $dataR = substr($value1['date'], 0, 10);
    $data = str_replace('-', '/', $dataR);

    if($data == $oggi){
      $result = $value1;
    }
  }


  if(isset($result['emotion'] )){
    $emotion = getEmotion($result,$email);

    switch ($emotion) {
      case 'gioia':
        if ($parameters['EmotionJoy'] != "") {
          $entity = $parameters['EmotionJoy'];
          $answer = "Yes, you are " . $entity;
        }else{
          $answer = "No, you're happy";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $answer = "Yes, you are " . $entity;
        }else{
          $answer = "No, you're scared";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $answer = "Yes, you are " . $entity;
        }else{
          $answer = "No, you're angry";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $answer = "Yes, you are " . $entity;
        }else{
          $answer = "No, you're disgusted";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $answer = "Yes, you are " . $entity;
        }else{
          $answer = "No, you're sad";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $answer = "Yes, you are " . $entity;
        }else{
          $answer = "No, you're surprised";
        }
        break;
      default:
          $answer = "No, you are not feeling any emotion";
        break;
    }
      
  }else{ //Se non sono presenti dati relativi ad oggi

      $param = "past";
      $json_data = queryMyrror($param,$email);
      $result = null;
      $max = "";
      $emotion = "";

    //Prendo l'ultima data disponibile
    foreach ($json_data['affects'] as $key1 => $value1) {
      $date = substr($value1['date'],0, 10);

      if($date > $max){
        $result = $value1;
        $max = $date;
      }
    }

    $emotion = getEmotion($result,$email);

     switch ($emotion) {
      case 'gioia':
        if ($parameters['EmotionJoy'] != "") {
          $entity = $parameters['EmotionJoy'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were happy";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were scared";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were angry";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were disgusted";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were sad";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $risposta = "Yes, you were " . $entity;
        }else{
          $risposta = "No, you were surprised";
        }
        break;
      default:
          $risposta = "You weren't feeling any emotion";
        break;
    }

    $answer = "Based on the latest data <br>" . $risposta;
  }

  return $answer;

}


function getLastEmotion($email){
  $param = "past";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $max = "";
  $emotion = "";

  //Prendo l'ultima data disponibile
  foreach ($json_data['affects'] as $key1 => $value1) {
    $date = substr($value1['date'],0, 10);

    if($date > $max){
      $result = $value1;
      $max = $date;
    }
  }

  $emotion = getEmotion($result,$email);

  return $emotion;
}