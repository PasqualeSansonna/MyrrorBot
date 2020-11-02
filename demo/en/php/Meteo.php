<?php
/*
questa funzione prende in ingresso il nome di una città
ed effettua una query in curl tramite la quale ottiene un file
json con le previsioni meteo dei prossimi 5 giorni per fascia oraria
*/
function queryWeather($city){

$ch = curl_init();
$json_data = null;
//$link = "api.openweathermap.org/data/2.5/forecast/hourly?q=Bari,IT&units=metric&APPID=dc994ecccf460974d34e32cde11ce679";
$link2 = "api.openweathermap.org/data/2.5/forecast?q=".$city.",IT&units=metric&APPID=dc994ecccf460974d34e32cde11ce679";
curl_setopt($ch, CURLOPT_URL,$link2);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);
$json_data = json_decode($server_output,true);
curl_close ($ch);

return $json_data;

}
/*
@city città da cercare
@parameters parametri sulle date
@text testo digitato dall'utente
questa funzione prende tutti i dati meteo odierni
e li restituisce come risposta
*/
function getTodayWeather($city,$parameters,$text){

$json_data = queryWeather($city);
if($json_data['cod'] != 200)
  return "";
$today = date('Y-m-d');
$result ="" ;
foreach ($json_data['list'] as $key => $value) {
    $data = substr($value['dt_txt'], 0,10);
    $hour = substr($value['dt_txt'], 11,2);
    $temp = 0;
    $description = "";
     if($data == $today && $hour >= 3){
        $temp = $value['main']['temp'];
            
          foreach ($value['weather'] as $key2 => $value2) {
           $val = 	$value2['main'];
           $description = $value2['description'];
          }
          switch ($description) {
          	case 'clear sky':
          		$condition = "cielo sereno";
          		break;

          	case 'few clouds':
          		$condition = "poco nuvoloso";
          		break;

          	case 'broken clouds':
          		$condition = "parzialmente nuvoloso";
          		break;

          	case 'scattered clouds':
          		$condition = "nubi sparse";
          		break;

          	case 'moderate rain':
          		$condition = "piogge modeste";
          		break;

          	case 'overcast clouds':
          		$condition = "nuvoloso";
          		break;

          	case 'light rain':
          		$condition = "pioggia leggera";
          		break;

          	case 'heavy rain':
          		$condition = "pioggia pesante";
          		break;
            
            case 'light snow':
              $condition = "neve leggera";
              break;

             case 'snow':
              $condition = "neve";
              break;
              
          	
          	default:
          		# code...
          		break;
          }
      $result.= $data.";".$hour.":00;".$temp.";".$condition."<br>";
      // $result .= "<br>";
     }
   
	
}

return $result;

}

function getCityWeather($parameters,$text){

if(isset($parameters['geo-city']) && $parameters['geo-city'] != ""){

$city = $parameters['geo-city'];
return getWeather($city,$parameters,$text);

}else{
  return "";
}



}

/*
@city città da cercare
@parameters parametri sulle date
@text testo digitato dall'utente
questa funzione analizza la data presente in parameters 
ottenuta tramite dialogflow ed effettua una ricerca tramite queryWeather
della citta selezionata. Prende quindi tutti i dati sul meteo relativi alla
data presente in parameters e restituisce il meteo e la temperatura per 
fascia oraria

*/
function getWeather($city,$parameters,$text){

if (isset($parameters['geo-city']) && $parameters['geo-city'] != "") {
  $city = $parameters['geo-city'];
}

if(isset($parameters['date']) && $parameters['date'] != ""){
$date = substr($parameters['date'],0,10);
}elseif(isset($parameters['date-period']['startDate']) && isset($parameters['date-period']['startDate']) != ""){

$date1 = substr($parameters['date-period']['startDate'],0,10);
$date2 = substr($parameters['date-period']['endDate'],0,10);
$json_data = queryWeather($city);
if($json_data['cod'] != 200)
  return "";
$result ="" ;
$i = 0;
foreach ($json_data['list'] as $key => $value) {
    $data = substr($value['dt_txt'], 0,10);
    $tmp = strtotime($data);
    $timestamp1 = strtotime($date1);
    $timestamp2 = strtotime($date2);
    $hour = substr($value['dt_txt'], 11,2);
    $temp = 0;
    $description = "";
   
     if($tmp >= $timestamp1 && $tmp <= $timestamp2 && $hour == 12){
        $temp = $value['main']['temp'];
       
            
          foreach ($value['weather'] as $key2 => $value2) {
           $val =   $value2['main'];
           $description = $value2['description'];
          }
          switch ($description) {
            case 'clear sky':
              $condition = "cielo sereno";
              break;

            case 'few clouds':
              $condition = "poco nuvoloso";
              break;

            case 'broken clouds':
              $condition = "parzialmente nuvoloso";
              break;

            case 'scattered clouds':
              $condition = "nubi sparse";
              break;

            case 'moderate rain':
              $condition = "piogge modeste";
              break;

            case 'overcast clouds':
              $condition = "nuvoloso";
              break;

            case 'light rain':
              $condition = "pioggia leggera";
              break;

            case 'heavy rain':
              $condition = "pioggia pesante";
              break;
            
            default:
              # code...
              break;
          }
          if($i == 0){
              $result.= $data.";".$hour.":00;".$temp.";".$condition."<br>";
              $i++;
          }else{
            $result.= $data.";".$data."; ".$temp.";".$condition."<br>";
          }
     
     }
   
  
}


  return array('res'=>$result,'city'=>$city);
 

}elseif(stripos($text,"odiern") !== false ){

$date = date('Y-m-d');
}else{
//prendiamo la data di domani di default se non ci sono dati
$date = date('Y-m-d',strtotime("+1days")); 
}

$json_data = queryWeather($city);
if($json_data['cod'] != 200)
  return "";
$result ="" ;
foreach ($json_data['list'] as $key => $value) {
    $data = substr($value['dt_txt'], 0,10);
    $hour = substr($value['dt_txt'], 11,2);
    $temp = 0;
    $description = "";
     if($data == $date && $hour >= 3){
        $temp = $value['main']['temp'];
            
          foreach ($value['weather'] as $key2 => $value2) {
           $val = 	$value2['main'];
           $description = $value2['description'];
          }
          switch ($description) {
          	case 'clear sky':
          		$condition = "cielo sereno";
          		break;

          	case 'few clouds':
          		$condition = "poco nuvoloso";
          		break;

          	case 'broken clouds':
          		$condition = "parzialmente nuvoloso";
          		break;

          	case 'scattered clouds':
          		$condition = "nubi sparse";
          		break;

          	case 'moderate rain':
          		$condition = "piogge modeste";
          		break;

          	case 'overcast clouds':
          		$condition = "nuvoloso";
          		break;

          	case 'light rain':
          		$condition = "pioggia leggera";
          		break;

          	case 'heavy rain':
          		$condition = "pioggia pesante";
          		break;
          	
          	default:
          		# code...
          		break;
          }
      $result.= $data.";".$hour.":00;".$temp.";".$condition."<br>";
     }
   
	
}

return array('res'=>$result,'city'=>$city);


}
/*
@city città da cercare
@parameters parametri sulle date
@text testo digitato dall'utente
questa funzione analizza la data presente in parameters 
ottenuta tramite dialogflow ed effettua una ricerca tramite queryWeather
della citta selezionata. Prende quindi tutti i dati sul meteo relativi alla
data presente in parameters. Analizza in seguito i token presenti nella
variabile text, in base a questi e ai dati sul meteo costruisce
una risposta binaria da fornire all'utente.

*/

function binaryWeather($city,$parameters,$text){


if(isset($parameters['date'])){
  $date = substr($parameters['date'],0,10);
}else{
  $date = date('Y-m-d'); 
}

if (isset($parameters['geo-city'])) {
  $json_data = queryWeather($parameters['geo-city']);
}else{
  $json_data = queryWeather($city);
}

if(isset($parameters['date-period']['startDate']) && isset($parameters['date-period']['startDate']) != ""){
  $date = substr($parameters['date-period']['startDate'], 0,10);
}

if($json_data['cod'] != 200)
  return "";
$answer ="" ;
$arr = array();

foreach ($json_data['list'] as $key => $value) {
    $data = substr($value['dt_txt'], 0,10);
    $hour = substr($value['dt_txt'], 11,2);
    $temp = 0;
    $description = "";
     if($data == $date && $hour >= 3){
        $temp = $value['main']['temp'];
            
          foreach ($value['weather'] as $key2 => $value2) {
           $val =   $value2['main'];
           array_push($arr, $val);
           $description = $value2['description'];
          }

     }
   }


      $result= array_count_values ($arr);
    
   if (strpos($text, 'rain') !== false || strpos($text,'bad') !== false) {
      
    if(isset($result['Rain']) && $result['Rain'] > 0){
      $answer = "Yes, it will rain";
    }else{
      $answer = "No, it'won't rain";
    }

   }elseif (strpos($text,'worse') !== false || strpos($text, 'crap') !== false) {
    
    if(isset($result['Rain']) && $result['Rain'] > 0){
      $answer = "Yes, it'll be rainy";
    }elseif(isset($result['Cloud']) && $result['Cloud'] >= 3 ){
      $answer = "Yes, it'll be cloudy";
    }else {
      $answer = "No,it'll be sunny";
    }

   }elseif (strpos($text, 'cloudy') !== false) {
 
      if(isset($result['Cloud']) && $result['Cloud'] >= 3 ){
      $answer = "Yes, it'll be cloudy";
    }elseif(isset($result['Rain']) && $result['Rain'] > 0) {
      $answer = "No, it'll be rainy";
    }else{
      $answer = "no, it'll be sunny";
    }

    
   }elseif(strpos($text, 'sun') !== false || strpos($text, 'nice') !== false || strpos($text, 'bel') !== false){

     if(isset($result['Cloud']) && $result['Cloud'] >= 3 ){
      $answer = "No, it'll be cloudy";
    }elseif(isset($result['Rain']) && $result['Rain'] > 0) {
      $answer = "No, it'll be rainy";
    }else{
      $answer = "Yes, it'll be sunny";
    }

   }
  
return array('res'=>$answer,'city'=>$city);



}

?>