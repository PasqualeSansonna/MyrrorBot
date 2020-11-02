<?php

/*Permette di fornire dati in relazione alla domanda richiesta
Viene fornito un flag per determinare se ci si riferisce alle emozioni oppure alla personalità
*/ 
function getSentiment($flag, $resp, $parameters, $email){

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
      $answer = "Sei di buon umore";
    }else if($mood == -1){
      $answer = "Sei di cattivo umore";
    }else{
      $answer = "Il tuo umore è neutro";
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
      $response = "eri di buon umore";
    }else if($mood == -1){
      $response = "eri di cattivo umore";
    }else{
      $response = "il tuo umore era neutro";
    }
    
    $answer = "In base agli ultimi dati rilevati " . $response;
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
      $answer = "Eri di buon umore";
    }else if($mood == -1){
      $answer = "Eri di cattivo umore";
    }else{
      $answer = "Il tuo umore era neutro";
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
      $response = "eri di buon umore";
    }else if($mood == -1){
      $response = "eri di cattivo umore";
    }else{
      $response = "il tuo umore era neutro";
    }
    
   $answer = "Secondo gli ultimi dati rilevati " . $response;
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
    $answer =  "Stavi provando " . $emotion ;

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
    $answer = "Basandomi sugli ultimi dati rilevati stavi provando " . $emotion;

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
        $answer = "Stai provando " . $result;
        break;
      case '2':
        $answer = "In questo momento provi " . $result;
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
   $answer = "Basandomi sugli ultimi dati rilevati stavi provando " . $emotion;
  }

  return $answer;

}

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
    }else if (strpos($result['emotion'] , 'none')   !== false ){
      $emotion = 'neutro';
    }else{
         return "al momento non stai provando alcuna emozione";
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
        $answer = "Si, eri di buon umore";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $answer = "No, il tuo umore era pessimo";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $answer = "No, il tuo umore era neutro";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $answer = "Si, eri di pessimo umore";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, il tuo umore era positivo";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, il tuo umore era neutro";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $answer = "Si, avevi un umore neutro";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, il tuo umore era positivo";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, il tuo umore era negativo";
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
        $risposta = "eri di buon umore";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $risposta = "il tuo umore era pessimo";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $risposta = "il tuo umore era neutro";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $risposta = "eri di pessimo umore";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $risposta = "il tuo umore era positivo";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $risposta = "il tuo umore era neutro";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $risposta = "avevi un umore neutro";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "il tuo umore era positivo";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "il tuo umore era negativo";
     }

       $answer = "Basandomi sugli ultimi dati rilevati " . $risposta;

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
        $answer = "Si, sei di buon umore";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $answer = "No, il tuo umore è pessimo";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $answer = "No, il tuo umore è neutro";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $answer = "Si, sei di pessimo umore";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, il tuo umore è positivo";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $answer = "No, il tuo umore è neutro";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $answer = "Si, hai un umore neutro";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, il tuo umore è positivo";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $answer = "No, il tuo umore è negativo";
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
        $risposta = "eri di buon umore";
     }else if ($mood == -1 && $parameters['UmoreBuono'] != ""){
        $risposta = "il tuo umore era pessimo";
     }else if ($mood == 0 && $parameters['UmoreBuono'] != ""){
        $risposta = "il tuo umore era neutro";
     }

    if ($mood == -1 && $parameters['UmoreCattivo'] != "") {
        $risposta = "eri di pessimo umore";
     }else if ($mood == 1 && $parameters['UmoreCattivo'] != ""){
        $risposta = "il tuo umore era positivo";
     }else if ($mood == 0 && $parameters['UmoreCattivo'] != ""){
        $risposta = "il tuo umore era neutro";
     }

      if ($mood == 0 && $parameters['UmoreNeutro'] != "") {
        $risposta = "avevi un umore neutro";
     }else if ($mood == 1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "il tuo umore era positivo";
     }else if ($mood == -1 && $parameters['UmoreNeutro'] != ""){
        $risposta = "il tuo umore era negativo";
     }


        $answer = "Basandomi sugli ultimi dati rilevati " . $risposta;
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
          $answer = "Si, eri " . $entity;
        }else{
          $answer = "No, eri felice";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $answer = "Si, eri " . $entity;
        }else{
          $answer = "No, eri spaventato";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $answer = "Si, eri " . $entity;
        }else{
          $answer = "No, eri arrabbiato";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $answer = "Si, eri " . $entity;
        }else{
          $answer = "No, eri disgustato";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $answer = "Si, eri " . $entity;
        }else{
          $answer = "No, eri triste";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $answer = "Si, eri " . $entity;
        }else{
          $answer = "No, eri sorpreso";
        }
        break;
      default:
          $answer = "No, non stavi provando alcuna emozione";
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
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri felice";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri spaventato";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri arrabbiato";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri disgustato";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri triste";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri sorpreso";
        }
        break;
      default:
          $risposta = "No, non stavi provando alcuna emozione";
        break;
    }

    $answer = "Basandomi sugli ultimi dati presenti <br>" . $risposta;

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
          $answer = "Si, sei " . $entity;
        }else{
          $answer = "No, sei felice";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $answer = "Si, sei " . $entity;
        }else{
          $answer = "No, sei spaventato";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $answer = "Si, sei " . $entity;
        }else{
          $answer = "No, sei arrabbiato";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $answer = "Si, sei " . $entity;
        }else{
          $answer = "No, sei disgustato";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $answer = "Si, sei " . $entity;
        }else{
          $answer = "No, sei triste";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $answer = "Si, sei " . $entity;
        }else{
          $answer = "No, sei sorpreso";
        }
        break;
      default:
          $answer = "No, non stai provando alcuna emozione";
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
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri felice";
        }
        break;
      case 'paura':
        if ($parameters['EmotionFear'] != "") {
          $entity = $parameters['EmotionFear'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri spaventato";
        }
        break;
      case 'rabbia':
        if ($parameters['EmotionAnger'] != "") {
          $entity = $parameters['EmotionAnger'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri arrabbiato";
        }
        break;
      case 'disgusto':
        if ($parameters['EmotionDisgust'] != "") {
          $entity = $parameters['EmotionDisgust'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri disgustato";
        }
        break;
      case 'tristezza':
        if ($parameters['EmotionSad'] != "") {
          $entity = $parameters['EmotionSad'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri triste";
        }
        break;
      case 'sorpresa':
        if ($parameters['EmotionSurprise'] != "") {
          $entity = $parameters['EmotionSurprise'];
          $risposta = "Si, eri " . $entity;
        }else{
          $risposta = "No, eri sorpreso";
        }
        break;
      default:
          $risposta = "Non stavi provando alcuna emozione";
        break;
    }

    $answer = "Basandomi sugli ultimi dati presenti <br>" . $risposta;
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