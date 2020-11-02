<?php


function getPeso($param, $email)
{

$param ="";
$json_data= queryMyrror($param,$email);
$peso=133; //preso da myrror, ma non presente nel json
$flag = false;

foreach ($json_data as $key1 => $value1) {
  if(isset($value1['weight'])){
    foreach ($value1['weight'] as $key2 => $value2) {
      if ($key2 == "value") {
        $peso = $value2;
        $flag = true;
      }   
    } 
  }
}

if($flag){
  return $peso['value'];
}else{
  return $peso;
}

}



function getAltezza($param, $email)
{
//echo"email di altezza". $email;
$param ="";
$json_data= queryMyrror($param,$email);
$altezza=198; //preso da myrror
$flag = false;


      foreach ($json_data as $key1 => $value1) {
        if(isset($value1['height'])) {
          foreach ($value1['height'] as $key2 => $value2) {
            if ($key2 == "value") {
              $altezza = $value2;
              $flag = true;
            }   
          } 
        }
      }

  if($flag){
    return $altezza['value'];
  }else{
    return $altezza;
  }    
 
}



function fetchYesterdaydSleep($resp,$data,$email)
{

  $param = "";
  $json_data = queryMyrror($param,$email);
  $result = null;
  $answer = 410;
  $data = date('Y-m-d',strtotime("-1 days"));

  //cerco data di ieri
  foreach ($json_data as $key1 => $value1) {
    if(isset($value1['sleep'])){

          foreach ($value1['sleep'] as $key2 => $value2) {

               $timestamp = $value2['timestamp'];
               $tempDate = date('Y-m-d',$timestamp/1000);
               if($data == $tempDate)
                 $result = $value2;
          }
    }
  }

  if($result['minutesAsleep'] != null){
    //risposta con data di ieri corretta
   $minutesAsleep = $result['minutesAsleep'];
   return $minutesAsleep;
   

  }else{
    //risposta standard con ultima data
    //algoritmo ultima data
    foreach ($json_data as $key1 => $value1) {

      if(isset($value1['sleep'])){
        $max = -1;
        foreach ($value1['sleep'] as $key2 => $value2) {
         
             $timestamp = $value2['timestamp'];
             if($timestamp > $max){
              
              $result = $value2;
              $max = $timestamp;
              
             }
         }   
      }
    }


    if (isset($timestamp)) {
      $data2 = date('d-m-Y',$timestamp/1000);
      //echo "Gli ultimi dati in mio possesso sono relativi al ".$data2."";
    }else{
      //Non ci sono dati passati, restituisco default.
      return 400;
    }

    if($result['minutesAsleep'] != null){
        

       $minutesAsleep = $result['minutesAsleep'];
       return $minutesAsleep;

    }else{
      //Non riesco a recuperare i dati, restituisco default
      $answer = 400;
    }

     

  }
  return $answer;
}






function getName($email)
{

$param = "";
$json_data = queryMyrror($param,$email);
$name ='NOME FARLOCCO';


foreach ($json_data as $key1 => $value1) {
	
	if(isset($value1['name'] ) ){


		foreach ($value1['name'] as $key2=> $value2) {

			if($key2 == 'value'){
				return $value2;
			}
		}


	}


}

return $name;
} //fine get name


function getTodayEmotion($oggi,$email)
{
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

    $emotion = getDEmotion($result,$email);

    return $emotion;

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

    $emotion = getDEmotion($result,$email);
    return $emotion;
  }

  return $emotion;

}



function getDEmotion($result,$email){

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
      $emotion = 'neutralità';
    }else{
         return "al momento non stai provando alcuna emozione";
    }

  return $emotion;

}


?>