<?php
/*
il metodo serve a leggere il file json completo preso da
myrror e cercare al suo interno una data specificata,se la 
data viene trovata nel file verrà restituita insieme al valore
corrispondente di restingHeartRate; se non viene trovata la
data specificata verranno restituiti i dati dell'ultima data disponibile
@Parameters sono i parametri sui periodi temporali 
individuati da dialogflow
@data è la data da cercare nel file
return data e battito cardiaco
*/
function cardioToday($parameters,$data,$email){

$param = "";
$json_data = queryMyrror($param,$email);
$result = null;
$dateR = null;

foreach ($json_data as $key1 => $value1) {

if(isset($value1['heart'])){

   foreach ($value1['heart'] as $key2 => $value2) {
   
       $timestamp = $value2['timestamp'];
       $tempDate = date('Y-m-d',$timestamp/1000);
      
       if($tempDate == $data){        
        $result = $value2;
        $dateR = $tempDate;
       }
   }
}

}

if(isset($result['restingHeartRate'])){
   $heart = $result['restingHeartRate'];

}else{

foreach ($json_data as $key1 => $value1) {

if(isset($value1['heart'])){

   $max = -1;
   foreach ($value1['heart'] as $key2 => $value2) {
   
       $timestamp = $value2['timestamp'];
        $tempDate = date('Y-m-d',$timestamp/1000);
       if($timestamp > $max){
        
        $result = $value2;
        $max = $timestamp;
        $dateR = $tempDate;

       }
   }
}

}

if(isset($result['restingHeartRate'])){
   $heart = $result['restingHeartRate'];

}else{
  $heart = 0;
}

}

return  array('date' => $dateR, 'heart' => $heart);

}
/*
@startDate data iniziale dell'intervallo
@endDate data finale dell'intervallo
Il seguente metodo ricerca all'interno del file json
restituito da myrror il dato restingHeartRate di tutte le
date presenti nell'intervallo specificato, viene fatta così
una media dei valori del battito cardiaco. 
return media battito cardiaco al minuto 
*/
function cardioInterval($startDate,$endDate,$email){

$param = "";
$json_data = queryMyrror($param,$email);
$count = 0;
$sum = 0;

foreach ($json_data as $key1 => $value1) {

if(isset($value1['heart'])){

   foreach ($value1['heart'] as $key2 => $value2) {
   
       $timestamp = $value2['timestamp'];
       $tempDate = date('Y-m-d',$timestamp/1000);
       if($tempDate <= $endDate && $tempDate >= $startDate){        
        $sum  += $value2['restingHeartRate'];
        $count++;
       }
   }
}

}

if ($count != 0) {
  $average = $sum/$count;
}else{
  $average = 0;
}

return $average;

}

/*
@resp frase di risposta standard ricevuta da dialogflow
@parameters parametri contenenti le info sui periodi di tempo
nella frase rilevati da dialogflow
@text frase scritta dall'utente
Il metodo controlla la presenza in parameters di date o
date-period e a seconda dei casi chiama il metodo corrispondente 
per ottenere i dati del battito cardiaco di un singolo giorno o 
di un intervallo di tempo. Nel caso nel file json non troviamo
i dati del giorno o del periodo scelto la risposta verrà costruita
utilizzando gli ultimi dati disponibli.
@return risposta da stampare a schermo

*/
function getCardio($resp,$parameters,$text,$email){

  $answer = "";
  $today = date("Y-m-d"); 
  $yesterday = date("Y-m-d",strtotime("-1 days")); 


  if(isset($parameters['date']) ){

    $date1 = substr($parameters['date'],0,10);

    if($today ==  $date1){

      //dati oggi
      $arr = cardioToday($parameters,$today,$email);
      
      if($arr['date'] == $today){
        
        /*
        la risposta di default ($resp) restituita da dialogflow è
        costruita per la data di oggi, così sostituiamo alla X presente 
        in $resp il valore del battito cardiaco da stampare
        */
        $answer = str_replace('X',$arr['heart'],$resp);
      }else{

        //risposta standard
        $answer = "The latest data in my possession relates to ".$arr['date']
        .". The heart rate is ".$arr['heart']." bpm";
      }

    }elseif($yesterday ==  $date1){

      //dati ieri
      $arr = cardioToday($parameters,$yesterday,$email);

      if($arr['date'] == $yesterday){
        $answer = "Your heart rate was ".$arr['heart']." bpm"; //risposta oggi
      }else{

        //risposta standard
        $answer = "The latest data in my possession relates to ".$arr['date']
        .". Your heart rate is ".$arr['heart']." bpm";
      }

   }elseif(isset($parameters['date-period']['startDate'])){

    //dati ultimo giorno trovato
    $startDate =  substr($parameters['date-period']['startDate'],0,10);
    $endDate =  substr($parameters['date-period']['endDate'],0,10);
    $average = cardioInterval($startDate,$endDate,$email);

    if($average != 0){
      $answer = "On average, your heart rate is ".$average." bpm.";
    }else{
      $arr = cardioToday($parameters,"",$email);
      $answer = "The latest data in my possession relates to ".$arr['date']
        ." and the heart rate was equal to ".$arr['heart']." bpm";
    }

    }else{
       $arr = cardioToday($parameters,"",$email);
       $answer = "The latest data in my possession relates to ".$arr['date']
        ." and the heart rate was equal to ".$arr['heart']." bpm";
    }

  }elseif (isset($parameters['date-period']['startDate'])) {

    //dati intervallo di tempo
    $startDate =  substr($parameters['date-period']['startDate'],0,10);
    $endDate =  substr($parameters['date-period']['endDate'],0,10);
    $average = cardioInterval($startDate,$endDate,$email);
    if($average != 0){
      $answer = "On average, your heart rate is ".$average." bpm.";
    }else{
      $arr = cardioToday($parameters,"",$email);
      $answer = "The latest data in my possession relates tol ".$arr['date']
        ." and the heart rate was equal to ".$arr['heart']." bpm";
    }

  }else{

   //dati ultimo giorno trovato
     $arr = cardioToday($parameters,"",$email);
     $answer = "The latest data in my possession relates tol ".$arr['date']
        ." and the heart rate was equal to ".$arr['heart']." bpm";
  }
  return $answer;

}


/*
@resp frase di risposta standard ricevuta da dialogflow
@parameters parametri contenenti le info sui periodi di tempo
nella frase rilevati da dialogflow
@text frase scritta dall'utente
Il metodo serve a costruire delle risposte binarie (si,no) per rispondere
a specifiche domande dell'utente.Le risposte saranno costruite tramite
i token riconosciuti nel testo,in particolare vengono distinti
buono/ottimo da pessimo/cattivo.
Viene effettuato un controllo sui parametri per verificare se abbiamo dati 
riguardanti una singola data o un intervallo. Nel caso non ci siano parametri 
con riferimenti al tempo utilizzeremo la data odierna. Se non vengono trovati 
dati nella data odierna verrà costruita una risposta utilizzando gli 
ultimi dati presenti nel file.
@return risposta da stampare a schermo
*/
function getCardioBinary($resp,$parameters,$text,$email){

    $answer = "";
    $today = date("Y-m-d");
    //$today = "2019-03-27";

    $yesterday = date("Y-m-d",strtotime("-1 days")); 

    if(isset($parameters['date-period']['startDate'])){

      $startDate =  substr($parameters['date-period']['startDate'],0,10);
      $endDate =  substr($parameters['date-period']['endDate'],0,10);
      $average = cardioInterval($startDate,$endDate,$email);

      if($average == 0){
        $answer = "I was not able to retrieve data for the period you indicated to me &#x1F62D;"; 
      }else{
          if(strpos($text, 'good') || strpos($text, 'great') || strpos($text, 'optimal') || strpos($text, 'excellent') || strpos($text, 'in the norm') || strpos($text, 'okay')){
       
            if($average >= 60 && $average <= 100){
              $answer = "Yes, on average your pulse is normal. In fact I have detected ".$average." bpm";
            }else{
              $answer = "No, on average your pulse is not normal. In fact I have detected ".$average." bpm";
            }

          }elseif (strpos($text, 'bad') || strpos($text, 'terrible') || strpos($text, 'bery bad') ||
            strpos($text, 'out of shape') || strpos($text, 'not okay') ) {
        
            if($average >= 60 && $average <= 100){
             $answer = "No, on average your pulse is normal. In fact I have detected ".$average." bpm";
            }else{
             $answer = "Yes, on average your pulse is not normal. In fact I have detected ".$average." bpm";
            }

          }
      }

    }elseif (isset($parameters['date'])) {

      $date1 = substr($parameters['date'],0,10);
      switch ($date1) {

        case $today:
          $arr = cardioToday($parameters,$today,$email);

          if(strpos($text, 'good') || strpos($text, 'great') || strpos($text, 'optimal') || strpos($text, 'excellent') || strpos($text, 'in the norm') || strpos($text, 'okay')){

            if($arr['date'] == $today){

                if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                  $answer = "Yes, your pulse is normal. In fact I have detected ".$arr['heart']." bpm";
                else
                   $answer = "No, your pulse is not normal. In fact I have detected ".$arr['heart']." bpm";
            }else{

               if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                  $answer = "The latest data in my possession relates to ".$arr['date'].
                            ". Your pulse was normal, in fact I detected ".$arr['heart']." bpm";
               }else{

                   $answer = "The latest data in my possession relates to ".$arr['date'].
                   ". Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
               }
                 
            }
                    
            }elseif (strpos($text, 'bad') || strpos($text, 'terrible') || strpos($text, 'bery bad') ||
                strpos($text, 'out of shape') || strpos($text, 'not okay') ) {

               if($arr['date'] == $today){
                if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                  $answer = "No, your pulse is normal. In fact I have detected ".$arr['heart']." bpm";
                else
                   $answer = "Yes, your pulse is not normal. In fact I have detected  ".$arr['heart']." bpm";
            }else{
               if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                  $answer = "The latest data in my possession relates to ".$arr['date'].
                            ". Your pulse was normal, that is ".$arr['heart']." bpm";
               }else{

                   $answer = "The latest data in my possession relates to ".$arr['date'].
                   ". Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
               }
                 
            }

            }

            break;
        case $yesterday:
        
          $arr = cardioToday($parameters,$yesterday,$email);  
          if(strpos($text, 'good') || strpos($text, 'great') || strpos($text, 'optimal') || strpos($text, 'excellent') || strpos($text, 'in the norm') || strpos($text, 'okay')){

          if($arr['date'] == $yesterday){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "Yes, your pulse was normal, in fact I detected ".$arr['heart']." bpm";
              else
                 $answer = "No, your pulse was not normal, in fact I found ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "The latest data in my possession relates to ".$arr['date'].
                          ". Your pulse was normal, in fact I detected ".$arr['heart']." bpm";
             }else{

                 $answer = "The latest data in my possession relates to ".$arr['date'].
                 ". Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
             }
               
          }
                  
          }elseif (strpos($text, 'bad') || strpos($text, 'terrible') || strpos($text, 'bery bad') ||
                strpos($text, 'out of shape') || strpos($text, 'not okay') ) {

             if($arr['date'] == $yesterday){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "No, your pulse was normal, in fact I detected  ".$arr['heart']." bpm";
              else
                 $answer = "Yes, your pulse was not normal, in fact I found  ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "The latest data in my possession relates to ".$arr['date'].
                          ". Your pulse was normal, in fact I detected  ".$arr['heart']." bpm";
             }else{

                 $answer = "The latest data in my possession relates to ".$arr['date'].
                 ". Your pulse was not normal, in fact I detected  ".$arr['heart']." bpm";
             }
               
          }

          }
       
          break;
        default:

             //ultima data disponibile
             $arr = cardioToday($parameters,"",$email);
            if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "The latest data in my possession relates to ".$arr['date'].
                          ". Your pulse was normal, in fact I detected ".$arr['heart']." bpm";
             }else{

                 $answer = "The latest data in my possession relates to ".$arr['date'].
                 ". Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
             }
          break;
      }
      
    }else{
         $arr = cardioToday($parameters,$today,$email);
             if(strpos($text, 'good') || strpos($text, 'great') || strpos($text, 'optimal') || strpos($text, 'excellent') || strpos($text, 'in the norm') || strpos($text, 'okay')){

          if($arr['date'] == $today){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "Yes, your pulse is normal, in fact I found ".$arr['heart']." bpm";
              else
                 $answer = "Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "The latest data in my possession relates to ".$arr['date'].
                          ". Your pulse was normal, in fact I detected ".$arr['heart']." bpm";
             }else{

                 $answer = "The latest data in my possession relates to ".$arr['date'].
                 ". Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
             }
               
          }
                  
          }elseif (strpos($text, 'bad') || strpos($text, 'terrible') || strpos($text, 'bery bad') ||
                strpos($text, 'out of shape') || strpos($text, 'not okay') ) {

             if($arr['date'] == $today){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "No, your pulse is normal, in fact I found ".$arr['heart']." bpm";
              else
                 $answer = "Yes, your pulse is not normal, in fact I have detected ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "The latest data in my possession relates to ".$arr['date'].
                          ". Your pulse was normal, in fact I detected ".$arr['heart']." bpm";
             }else{

                 $answer = "The latest data in my possession relates to ".$arr['date'].
                 ". Your pulse was not normal, in fact I detected ".$arr['heart']." bpm";
             }
               
          }

          }
    }

    return $answer;

}

/*
@resp frase di risposta standard ricevuta da dialogflow
@parameters parametri contenenti le info sui periodi di tempo
nella frase rilevati da dialogflow
@text frase scritta dall'utente
il metodo analizza i parameters se è presente la data di ieri
o di oggi chiama il metodo yestSleepBinary per ottenere i minutes di 
sonno dell'ultima notte,altrimenti viene fatta una distinzione in base al 
verbo riconosciuto da dialogflow, se i verbi sono al passato prossimo
viene chiamata la funzione yestSleepBinary altrimenti viene chiamata la 
funzione pastSleepBinary che costruisce la risposta con i dati storici
return risposta da stampare  
*/
function getSleepBinary($resp,$parameters,$text,$email){


$yesterday = date("Y-m-d",strtotime("-1 days")); 
if(isset($parameters['date'])  ||  isset($parameters['Passato'])){
$date1 = substr($parameters['date'],0,10);

if($date1 >= $yesterday){
//dati di ieri
  
 $answer = yestSleepBinary($resp,$parameters,$text,$yesterday,$email);


}else if($parameters['Passato']){
  //dati di ieri
  
  $answer = yestSleepBinary($resp,$parameters,$text,$yesterday,$email);
  //$answer = yestSleepBinary($resp,$parameters,$text,'2019-02-22');

}else{
  //dati storici
  $answer = pastSleepBinary($resp,$parameters,$text,$email);
}

}else{
  //dati storici
   $answer = pastSleepBinary($resp,$parameters,$text,$email);
}

return $answer;

}
/*
@resp frase di risposta standard ricevuta da dialogflow
@parameters parametri contenenti le info sui periodi di tempo
nella frase rilevati da dialogflow
@text frase scritta dall'utente
la funzione effettua una media dei minutes trascorsi nel letto
e dei minutes di sonno, successivamente viene costruita una risposta
verificando le parole presenti all'interno della frase digitata 
dall'utente e usando dei valori soglia (390 minutes di sonno) per
rispondere in maniera positiva o negativa
return risposta da stampare
*/
function pastSleepBinary($resp,$parameters,$text,$email){

  $param = "";
  $json_data = queryMyrror($param,$email);
  $result = "";

  $count = 0;
  $sumInBed = 0;
  $sumAsleep = 0;

  foreach ($json_data as $key1 => $value1) {

    if(isset($value1['sleep'])){
      
    //ricerca per periodo   
     foreach ($value1['sleep'] as $key2 => $value2) {
         $sumInBed += $value2['timeInBed'];
         $sumAsleep += $value2['minutesAsleep'];
         $count++;         
      }
    }

  }

  if($count == 0){
    //non ci sono riferimenti per quel periodo
    return "I was not able to retrieve data for the period you indicated to me &#x1F62D;";
  }
  $asleepAV = intval($sumAsleep/$count);
  $inBedAV =intval($sumInBed/$count);

  //Conversione minutes in hours and minutes
  if ($asleepAV < 1) {
    return "you haven't slept &#x1F631;";
  }
  $hours = floor($asleepAV / 60);
  $minutes = ($asleepAV % 60);

  if(strpos($text, 'enough')){

     if($asleepAV >= 390){
          if ($hours == 1) {
            $result = "Yes, you sleep enough. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
          }else{
            $result = "Yes, you sleep enough. On average you sleep" .$hours. " hours and " . $minutes . " minutes";
          }
     }else{
        if ($hours == 1) {
          $result = "No, you don't sleep enough. On average you sleep" .$hours. " hour and " . $minutes . " minutes";
        }else{
          $result = "No, you don't sleep enough. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
      }
  }elseif (strpos($text, 'more')) {

     if($asleepAV >= 390){
        if ($hours == 1) {
          $result = "Yes, you sleep a lot. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
        }else{
            $result = "Yes, you sleep a lot. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
      }else{
        if ($hours == 1) {
          $result = "No, you don't sleep much. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
        } else{
          $result = "No, you don't sleep much. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
      }

  }elseif (strpos($text, 'good')) {

     if($asleepAV >= 390){
        if ($hours == 1) {
         $result = "Yes, you sleep well. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
        }else{
         $result = "Yes, you sleep well. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
     }else{
        if ($hours == 1) {
         $result = "No, you don't sleep well. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
        }else{
          $result = "No, you don't sleep well. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
      }
  }elseif (strpos($text, 'less')) {
      if($asleepAV >= 480){
        if ($hours == 1) {
          $result = "Yes, you should sleep less. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
        }else{
          $result = "Yes, you should sleep less. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
     }else{
        if ($hours == 1) {
         $result = "No, you sleep enough. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
        }else{
          $result = "No, you sleep enough. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
        }
    }
  }elseif (strpos($text, 'most')) {

     if($asleepAV >= 390){
      if ($hours == 1) {
       $result = "No, you shouldn't sleep any more. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
      }else{
        $result = "No, you shouldn't sleep any more. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
      }
     }else{
      if ($hours == 1) {
        $result = "Yes, you should sleep more. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
      }else{
        $result = "Yes, you should sleep more. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
      }
    }
    
  }elseif (strpos($text, 'little')){
    
     if($asleepAV >= 390){
      if ($hours == 1) {
       $result = "No, you sleep enough. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
      }else{
        $result = "No, you sleep enough. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
      }
     }else{
      if ($hours == 1) {
       $result = "Yes, you should sleep more. On average you sleep " .$hours. " hour and " . $minutes . " minutes";
      }else{
       $result = "Yes, you should sleep more. On average you sleep " .$hours. " hours and " . $minutes . " minutes";
      }
     }

  }else{
    if ($hours == 1) {
      $result = "On average you sleep " .$hours. " hour and " . $minutes . " minutes";;
    }else{
      $result = "On average you sleep " .$hours. " hours and " . $minutes . " minutes";;
    }
  }

   return $result;
}

/*
@resp frase di risposta standard ricevuta da dialogflow
@parameters parametri contenenti le info sui periodi di tempo
nella frase rilevati da dialogflow
@text frase scritta dall'utente
@data da cercare
la funzione ricerca all'interno del file json la data che viene passata
come parametro , se non la trova verrà presa l'ultima data disponibile ,
questa distinzione avviene tramite il flag.
Viene costruita una risposta in base ai token rilevati nella frase
 usando dei valori soglia (390 minutes di sonno) per
rispondere in maniera positiva o negativa.
return risposta da stampare
*/
function yestSleepBinary($resp,$parameters,$text,$data,$email){

  $param = "";
  $json_data = queryMyrror($param,$email);
  $result = null;

  //serve a capire se vengono presi i dati della data corretta oppure gli ultimi presenti nel file
  $flag = false;

  //cerco data di ieri
  foreach ($json_data as $key1 => $value1) {
    if(isset($value1['sleep'])){

      foreach ($value1['sleep'] as $key2 => $value2) {

         $timestamp = $value2['timestamp'];
         $tempDate = date('Y-m-d',$timestamp/1000);
         if($data == $tempDate){
           $result = $value2;
         }
      }
    }
  }

  if($result['minutesAsleep'] != null){
    
    //risposta con data di ieri corretta
    $minutesAsleep = $result['minutesAsleep'];
    $timeinbed = $result['timeInBed'];
    $flag = true;

  }else{

    /*risposta standard con ultima data
    algoritmo ultima data*/
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
    }else{
      return "I was unable to retrieve data related to your sleep &#x1F62D; Check if they are present in your profile!";
    }

    if($result['minutesAsleep'] != null){
      $data = $data2;
      $minutesAsleep = $result['minutesAsleep'];
      $timeinbed = $result['timeInBed'];

    }else{
      return "I was unable to retrieve data related to your sleep &#x1F62D; Check if they are present in your profile!";
    }
  }

  //Conversione minutes in hours and minutes
  if ($minutesAsleep < 1) {
    return "You don't sleep &#x1F631;";
  }
  $hours = floor($minutesAsleep / 60);
  $minutes = ($minutesAsleep % 60);


  if(strpos($text, 'enough') || strpos($text, 'good')){

    if($minutesAsleep >= 390 ){
       
       if($flag == true){
          if ($hours == 1) {
            $answer = "Yes, you've slept enough. You slept well ".$hours. " hour e " . $minutes . " minutes";
          }else{
            $answer = "Yes, you've slept enough. You slept well ".$hours. " hours e " . $minutes . " minutes";
          }
       }else{
          if ($hours == 1) {
            $answer ="The last data is from ".$data." and you have slept enough. Or " .$hours. " hour e " . $minutes . " minutes"; 
          }else{
              $answer ="The last data is from ".$data." and you have slept enough. Or ".$hours. " hours e " . $minutes . " minutes";   
          }
          
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, you haven't slept enough. You only slept for ".$hours. " hour e " . $minutes . " minutes";
          }else{
            $answer = "No, you haven't slept enough. You only slept for ".$hours. " hours e " . $minutes . " minutes";
          }
          
       }else{
        if ($hours == 1) {
          $answer ="The last data is from ".$data." and I see that you haven't slept enough. In fact only for  "
          .$hours. " hour e " . $minutes . " minutes";
        }else{
          $answer ="The last data is from ".$data." and I see that you haven't slept enough. In fact only for  "
          .$hours. " hours e " . $minutes . " minutes";
        }

       }

    }
/*
  }

  elseif( strpos($text, 'bene')){

      if($minutesAsleep >= 390 ){
       
       if($flag == true){
          if ($hours == 1) {
            $answer = "Si, hai dormito bene. Hai dormito ben ".$hours. " hour and " . $minutes . " minutes";
          }else{
            $answer = "Si, hai dormito bene. Hai dormito ben ".$hours. " hours and " . $minutes . " minutes";
          }
          
       }else{
        if ($hours == 1) {
            $answer ="The last data is from ".$data." e noto che hai dormito bene ovvero per ben "
          .$hours. " hour and " . $minutes . " minutes";
        }else{
            $answer ="The last data is from ".$data." e noto che hai dormito bene ovvero per ben "
          .$hours. " hours and " . $minutes . " minutes";
        }
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, non hai dormito bene. Hai dormito solo per ".$hours. " hour and " . $minutes . " minutes";
          }else{
            $answer = "No, non hai dormito bene. Hai dormito solo per ".$hours. " hours and " . $minutes . " minutes";
          }
          
       }else{
        if ($hours == 1) {
          $answer ="The last data is from ".$data." e non hai dormito molto bene. Infatti hai dormito solo per "
          .$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer ="The last data is from ".$data." e non hai dormito molto bene. Infatti hai dormito solo per "
          .$hours. " hours and " . $minutes . " minutes";
        }
          
       }

    }
*/
  }elseif (strpos($text, 'more')) {

      if($minutesAsleep >= 390 ){
       
       if($flag == true){
        if ($hours == 1) {
          $answer = "Yes, you've slept a lot. You slept well ".$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer = "Yes, you've slept a lot. You slept well ".$hours. " hours and " . $minutes . " minutes";
        }
          
       }else{
        if ($hours == 1) {
          $answer ="The last data is from ".$data." and I notice that you have slept so much. That is to say "
          .$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer ="The last data is from ".$data." and I notice that you have slept so much. That is to say "
          .$hours. " hours and " . $minutes . " minutes";
        }
          
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, you haven't slept much. You only slept for ".$hours. " hour and " . $minutes . " minutes";
          }else{
            $answer = "No, you haven't slept much. You only slept for ".$hours. " hours and " . $minutes . " minutes";
          }
          
       }else{
          if ($hours == 1) {
              $answer ="The last data is from ".$data." and I notice that you haven't slept so much. Only "
            .$hours. " hour and " . $minutes . " minutes";
          }else{
            $answer ="The last data is from ".$data." and I notice that you haven't slept so much. Only "
            .$hours. " hours and" . $minutes . " minutes";
          }
          
       }

    }
    
  }

  elseif(strpos($text, 'meno')){

      if($minutesAsleep >= 480 ){
       
         if($flag == true){
            if ($hours == 1) {
              $answer = "Yes, you should sleep less. I see you slept for ".$hours. " hour and " . $minutes . " minutes";
            }else{
              $answer = "Yes, you should sleep less. I see you slept for ".$hours. " hours and " . $minutes . " minutes";
            }
            
         }else{
            if ($hours == 1) {
                $answer ="The latest data is on  ".$data." and I know you should sleep less. You slept for "
              .$hours. " hour and " . $minutes . " minutes";
            }else{
              $answer ="The latest data is on  ".$data." and I know you should sleep less. You slept for "
              .$hours. " hours and " . $minutes . " minutes";
            }
         }
       
      }else{
          if($flag == true){
            if ($hours == 1) {
              $answer = "No, you haven't slept enough. You only slept for ".$hours. " hour and " . $minutes . " minutes";
            }else{
              $answer = "No, you haven't slept enough. You only slept for ".$hours. " hours and " . $minutes . " minutes";
            }
            
           }else{
              if ($hours == 1) {
                $answer ="The last data is from ".$data." and I notice that you haven't slept enough. Only "
                .$hours. " hour and " . $minutes . " minutes";
              }else{
                $answer ="The last data is from ".$data." and I notice that you haven't slept enough. Only "
                .$hours. " hours and " . $minutes . " minutes";
              }
           }

      }

  }elseif (strpos($text,'little')) {

    if($minutesAsleep >= 390 ){
       
       if($flag == true){
        if ($hours == 1) {
          $answer = "No, you've slept enough. In fact you slept ".$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer = "No, you've slept enough. In fact you slept ".$hours. " hours and " . $minutes . " minutes";
        }
          
       }else{
        if ($hours == 1) {
          $answer ="The last data is from ".$data." and I notice that you have slept enough or "
          .$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer ="The last data is from ".$data." and I notice that you have slept enough or "
          .$hours. " hours and " . $minutes . " minutes";
        }
          
       }
       
    }else{
      if($flag == true){
        if ($hours == 1) {
          $answer = "Yes, you should sleep more. You have slept ".$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer = "Yes, you should sleep more. You have slept ".$hours. " hours and " . $minutes . " minutes";
        }
          
      }else{
        if ($hours == 1) {
          $answer ="The last data is from ".$data." and I notice that you should sleep more. You only slept "
          .$hours. " hour and " . $minutes . " minutes";
        }else{
          $answer ="The last data is from ".$data." and I notice that you should sleep more. You only slept "
          .$hours. " hours and " . $minutes . " minutes";
        }
          
      }
    }

  }else{

      //Conversione minutes in hours and minutes
      if ($minutesAsleep < 1) {
        return "I don't know you slept &#x1F631;";
      }
      $hours = floor($minutesAsleep / 60);
      $minutes = ($minutesAsleep % 60);

      if ($hours == 1) {
          $answer = "You have slept ". $hours ." hour and " .$minutes . ' minutes'; 
      }else{
          $answer = "You have slept ". $hours ." hours and " .$minutes . ' minutes';
      }
  }

  return $answer;

}

/*
@resp frase di risposta standard ricevuta da dialogflow
@data da cercare
la funzione costruisce una risposta cercando la data passata come
parametro nel file, se questa data non viene trovata verranno 
presi i dati dell'ultima data disponibile. I dati verranno quindi inseriti
nella risposta restituita da dialogflow tramite la funzione str_replace.
return risposta da stampare
*/
function fetchYesterdaySleep($resp,$data,$email){

  $param = "";
  $json_data = queryMyrror($param,$email);
  $result = null;

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
   $timeinbed = $result['timeInBed'];

    //Conversione minutes in hours and minutes
    $hoursSleep = floor($minutesAsleep / 60);
    $minutesSleep = ($minutesAsleep % 60);

    //Conversione minutes in hours and minutes
    $hoursBed = floor($timeinbed / 60);
    $minutesBed = ($timeinbed % 60);

    $answer = str_replace("X1",$hoursSleep,$answer);
    $answer = str_replace('X2', $minutesSleep, $answer);
    $answer = str_replace("Y1",$hoursBed,$answer);
    $answer = str_replace('Y2', $minutesBed, $answer);

    return $answer;

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
    }else{
      return "I was unable to retrieve data related to your sleep &#x1F62D; Check if they are present in your profile!";
    }

    $answer = "The latest data in my possession relates to ".$data2."<br>";

    if($result['minutesAsleep'] != null){
        $answer .= $resp;

       $minutesAsleep = $result['minutesAsleep'];
       $timeinbed = $result['timeInBed'];

      //Conversione minutes in hours and minutes
      $hoursSleep = floor($minutesAsleep / 60);
      $minutesSleep = ($minutesAsleep % 60);

      //Conversione minutes in hours and minutes
      $hoursBed = floor($timeinbed / 60);
      $minutesBed = ($timeinbed % 60);

     $answer = str_replace("X1",$hoursSleep,$answer);
     $answer = str_replace('X2', $minutesSleep, $answer);
     $answer = str_replace("Y1",$hoursBed,$answer);
     $answer = str_replace('Y2', $minutesBed, $answer);

    }else{
      $answer = "I was unable to retrieve data for the period you indicated to me &#x1F62D;";
    }

     return $answer;

  }
}


/*
@resp frase di risposta standard ricevuta da dialogflow
@parameters parametri contenenti le info sui periodi di tempo
nella frase rilevati da dialogflow
@text frase scritta dall'utente
il metodo analizza i parameters se è presente la data di ieri,di oggi
oppure è stato riconosciuto un verbo al passato prossimo nella frase,
 chiama quindi il metodo fetchYesterdaySleep per ottenere i minutes di 
sonno dell'ultima notte,altrimenti viene chiamata la 
funzione fetchPastSleep che costruisce la risposta con i dati storici
return risposta da stampare  
*/
function getSleep($resp,$parameters,$text,$email){

  $yesterday = date("Y-m-d",strtotime("-1 days")); 
  $timestamp = strtotime($yesterday);



  if(isset($parameters['date'])  ||  isset($parameters['Passato']) || isset($parameters['date-period']) ){
  $date1 = substr($parameters['date'],0,10);

  //echo $yesterday;
  if($date1 == $yesterday){
    //dati di ieri 
   $answer = fetchYesterdaySleep($resp,$yesterday,$email);
    //$answer = fetchYesterdaySleep($resp,'2019-02-22');
  }else if(isset($parameters['date-period']['endDate']) && isset($parameters['date-period']['startDate'])){
   
   
   
  foreach ($parameters['date-period'] as $keyP => $valueP) {

    if($keyP == 'endDate' )
      $endDate = substr($valueP,0,10);
    else
      $startDate = substr($valueP,0,10);
    
  }

  $answer = fetchPastSleep($endDate,$startDate,$email);

  }else if(isset($parameters['Passato'])){
  //dati di ieri
     
  $answer = fetchYesterdaySleep($resp,$yesterday,$email);

  }else{
     
  //dati storici
    $answer = fetchPastSleep("","",$email);
  }

  }else{
    
  //dati storici
    $answer = fetchPastSleep("","",$email);
  }

  return $answer;

}
/*
@startDate data iniziale dell'intervallo
@endDate data finale dell'intervallo
questa funzione ricerca all'interno del file json i dati del
sonno dell'utente filtrati per data, effettua quindi una media 
dei dati sul sonno e costruisce la risposta da restituire all'utente
Se non vengono trovati dati viene effettuata una media su tutto il file

return risposta da stampare  
*/
function fetchPastSleep($endDate,$startDate,$email){

  $param = "";
  $json_data = queryMyrror($param,$email);
  $result = "";

  $count = 0;
  $sumInBed = 0;
  $sumAsleep = 0;
  foreach ($json_data as $key1 => $value1) {
    if(isset($value1['sleep'])){
       if($endDate != "" && $startDate != ""){
         //ricerca per periodo
        
      foreach ($value1['sleep'] as $key2 => $value2) {
        $timestamp = $value2['timestamp']; 
        $data = date('Y-m-d',$timestamp/1000);

        if($data >= $startDate && $data <= $endDate){
           $sumInBed += $value2['timeInBed'];
           $sumAsleep += $value2['minutesAsleep'];
           $count++;  
        }
      }
        $result = "from ".$startDate ." to ".$endDate;

       }else{


     foreach ($value1['sleep'] as $key2 => $value2) {
         
         $sumInBed += $value2['timeInBed'];
         $sumAsleep += $value2['minutesAsleep'];
         $count++;      
      
     }   
  }
  }


  }

  if($count == 0){
    //non ci sono riferimenti per quel periodo
    return fetchPastSleep("","",$email);
  }
  $asleepAV = intval($sumAsleep/$count);
  $inBedAV =intval($sumInBed/$count);

  //Conversione minutes in hours and minutes
  $hoursSleep = floor($asleepAV / 60);
  $minutesSleep = ($asleepAV % 60);

  //Conversione minutes in hours and minutes
  $hoursBed = floor($inBedAV / 60);
  $minutesBed = ($inBedAV % 60);

  $result .= " on average you slept ".$hoursSleep ." hours and " .$minutesSleep ." minutes, spending in bed ".$hoursBed." hours and " .$minutesSleep ." minutes";

  return $result;


}







