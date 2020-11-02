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
        $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date']
        .". Il battito cardiaco è di ".$arr['heart']." bpm";
      }

    }elseif($yesterday ==  $date1){

      //dati ieri
      $arr = cardioToday($parameters,$yesterday,$email);

      if($arr['date'] == $yesterday){
        $answer = "Il tuo battito cardiaco era di ".$arr['heart']." bpm"; //risposta oggi
      }else{

        //risposta standard
        $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date']
        .". Il battito cardiaco è di ".$arr['heart']." bpm";
      }

   }elseif(isset($parameters['date-period']['startDate'])){

    //dati ultimo giorno trovato
    $startDate =  substr($parameters['date-period']['startDate'],0,10);
    $endDate =  substr($parameters['date-period']['endDate'],0,10);
    $average = cardioInterval($startDate,$endDate,$email);

    if($average != 0){
      $answer = "In media, il tuo battito cardiaco è di ".$average." bpm.";
    }else{
      $arr = cardioToday($parameters,"",$email);
      $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date']
        ." ed il battito cardiaco era pari a ".$arr['heart']." bpm";
    }

    }else{
       $arr = cardioToday($parameters,"",$email);
       $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date']
        ." ed il battito cardiaco era pari a ".$arr['heart']." bpm";
    }

  }elseif (isset($parameters['date-period']['startDate'])) {

    //dati intervallo di tempo
    $startDate =  substr($parameters['date-period']['startDate'],0,10);
    $endDate =  substr($parameters['date-period']['endDate'],0,10);
    $average = cardioInterval($startDate,$endDate,$email);
    if($average != 0){
      $answer = "In media, il tuo battito cardiaco è di ".$average." bpm.";
    }else{
      $arr = cardioToday($parameters,"",$email);
      $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date']
        ." ed il battito cardiaco era pari a ".$arr['heart']." bpm";
    }

  }else{

   //dati ultimo giorno trovato
     $arr = cardioToday($parameters,"",$email);
     $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date']
        ." ed il battito cardiaco era pari a ".$arr['heart']." bpm";
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
        $answer = "Non sono riuscito a recuperare i dati relativi al periodo che mi hai indicato &#x1F62D;"; 
      }else{
          if(strpos($text, 'buono') || strpos($text, 'buone') || strpos($text, 'bene') || strpos($text, 'ottimo') || strpos($text, 'nella norma') || strpos($text, 'buona')){
       
            if($average >= 60 && $average <= 100){
              $answer = "Si, in media le tue pulsazioni sono nella norma. Infatti ho rilevato ".$average." bpm";
            }else{
              $answer = "No, in media le tue pulsazioni non sono nella norma. Infatti ho rilevato ".$average." bpm";
            }

          }elseif (strpos($text, 'pessimo') || strpos($text, 'cattivo') || strpos($text, 'cattive') ||
            strpos($text, 'male ') || strpos($text, 'fuori norma') ) {
        
            if($average >= 60 && $average <= 100){
             $answer = "No, in media le tue pulsazioni sono nella norma. Infatti ho rilevato ".$average." bpm";
            }else{
             $answer = "Si, in media le tue pulsazioni non sono nella norma. Infatti ho rilevato ".$average." bpm";
            }

          }
      }

    }elseif (isset($parameters['date'])) {

      $date1 = substr($parameters['date'],0,10);
      switch ($date1) {

        case $today:
          $arr = cardioToday($parameters,$today,$email);

          if(strpos($text, 'buono') || strpos($text, 'buone') || strpos($text, 'bene') || strpos($text, 'ottimo') 
            || strpos($text, 'nella norma') || strpos($text, 'buona')){

            if($arr['date'] == $today){

                if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                  $answer = "Si, le tue pulsazioni sono nella norma. Infatti ho rilevato ".$arr['heart']." bpm";
                else
                   $answer = "No, le tue pulsazioni non sono nella norma. Infatti ho rilevato ".$arr['heart']." bpm";
            }else{

               if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                  $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                            ". Le tue pulsazioni erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
               }else{

                   $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                   ". Le tue pulsazione non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
               }
                 
            }
                    
            }elseif (strpos($text, 'pessimo') || strpos($text, 'cattivo') || strpos($text, 'cattive') ||
                   strpos($text, 'male ') || strpos($text, 'fuori norma') ) {

               if($arr['date'] == $today){
                if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                  $answer = "No, le tue pulsazioni sono nella norma. Infatti ho rilevato ".$arr['heart']." bpm";
                else
                   $answer = "Si, le tue pulsazioni non sono nella norma. Infatti ho rilevato  ".$arr['heart']." bpm";
            }else{
               if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                  $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                            ". Le tue pulsazioni erano nella norma, ovvero ".$arr['heart']." bpm";
               }else{

                   $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                   ". Le tue pulsazione non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
               }
                 
            }

            }

            break;
        case $yesterday:
        
          $arr = cardioToday($parameters,$yesterday,$email);  
          if(strpos($text, 'buono') || strpos($text, 'buone') || strpos($text, 'bene') || strpos($text, 'ottimo') || strpos($text, 'nella norma') || strpos($text, 'buona')){

          if($arr['date'] == $yesterday){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "Si, le tue pulsazioni erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
              else
                 $answer = "No, le tue pulsazioni non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                          ". Le tue pulsazioni erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }else{

                 $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                 ". Le tue pulsazione non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }
               
          }
                  
          }elseif (strpos($text, 'pessimo') || strpos($text, 'cattivo') || strpos($text, 'cattive') ||
                 strpos($text, 'male ') || strpos($text, 'fuori norma') ) {

             if($arr['date'] == $yesterday){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "No, le tue pulsazioni erano nella norma, infatti ho rilevato  ".$arr['heart']." bpm";
              else
                 $answer = "Si, le tue pulsazioni non erano nella norma, infatti ho rilevato  ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                          ". Le tue pulsazioni erano nella norma, infatti ho rilevato  ".$arr['heart']." bpm";
             }else{

                 $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                 ". Le tue pulsazione non erano nella norma, infatti ho rilevato  ".$arr['heart']." bpm";
             }
               
          }

          }
       
          break;
        default:

             //ultima data disponibile
             $arr = cardioToday($parameters,"",$email);
            if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                          ". Le tue pulsazioni erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }else{

                 $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                 ". Le tue pulsazione non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }
          break;
      }
      
    }else{
         $arr = cardioToday($parameters,$today,$email);
             if(strpos($text, 'buono') || strpos($text, 'buone') || strpos($text, 'bene') || strpos($text, 'ottimo') || strpos($text, 'nella norma') || strpos($text, 'buona')){

          if($arr['date'] == $today){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "Si, le tue pulsazioni sono nella norma, infatti ho rilevato ".$arr['heart']." bpm";
              else
                 $answer = "No, le tue pulsazioni non sono nella norma, infatti ho rilevato ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                          ". Le tue pulsazioni erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }else{

                 $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                 ". Le tue pulsazione non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }
               
          }
                  
          }elseif (strpos($text, 'pessimo') || strpos($text, 'cattivo') || strpos($text, 'cattive') ||
                 strpos($text, 'male ') || strpos($text, 'fuori norma') ) {

             if($arr['date'] == $today){
              if($arr['heart'] >= 60 && $arr['heart'] <= 100)
                $answer = "No, le tue pulsazioni sono nella norma, infatti ho rilevato ".$arr['heart']." bpm";
              else
                 $answer = "Si,le tue pulsazioni non sono nella norma, infatti ho rilevato ".$arr['heart']." bpm";
          }else{
             if($arr['heart'] >= 60 && $arr['heart'] <= 100){
                $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                          ". Le tue pulsazioni erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
             }else{

                 $answer = "Gli ultimi dati in mio possesso sono relativi al ".$arr['date'].
                 ". Le tue pulsazione non erano nella norma, infatti ho rilevato ".$arr['heart']." bpm";
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
o di oggi chiama il metodo yestSleepBinary per ottenere i minuti di 
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
la funzione effettua una media dei minuti trascorsi nel letto
e dei minuti di sonno, successivamente viene costruita una risposta
verificando le parole presenti all'interno della frase digitata 
dall'utente e usando dei valori soglia (390 minuti di sonno) per
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
    return "Non sono riuscito a recuperare i dati relativi al periodo che mi hai indicato &#x1F62D;";
  }
  $asleepAV = intval($sumAsleep/$count);
  $inBedAV =intval($sumInBed/$count);

  //Conversione minuti in ore e minuti
  if ($asleepAV < 1) {
    return "Non mi risulta che tu abbia dormito &#x1F631;";
  }
  $hours = floor($asleepAV / 60);
  $minutes = ($asleepAV % 60);

  if(strpos($text, 'abbastanza')){

     if($asleepAV >= 390){
          if ($hours == 1) {
            $result = "Si, dormi abbastanza. In media dormi " .$hours. " ora e " . $minutes . " minuti";
          }else{
            $result = "Si, dormi abbastanza. In media dormi " .$hours. " ore e " . $minutes . " minuti";
          }
     }else{
        if ($hours == 1) {
          $result = "No, non dormi abbastanza. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        }else{
          $result = "No, non dormi abbastanza. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
      }
  }elseif (strpos($text, 'tanto')) {

     if($asleepAV >= 390){
        if ($hours == 1) {
          $result = "Si, dormi tanto. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        }else{
            $result = "Si, dormi tanto. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
      }else{
        if ($hours == 1) {
          $result = "No, non dormi tanto. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        } else{
          $result = "No, non dormi tanto. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
      }

  }elseif (strpos($text, 'bene')) {

     if($asleepAV >= 390){
        if ($hours == 1) {
         $result = "Si, dormi bene. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        }else{
         $result = "Si, dormi bene. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
     }else{
        if ($hours == 1) {
         $result = "No, non dormi bene. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        }else{
          $result = "No, non dormi bene. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
      }
  }elseif (strpos($text, 'di meno')) {
      if($asleepAV >= 480){
        if ($hours == 1) {
          $result = "Si, dovresti dormire di meno. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        }else{
          $result = "Si, dovresti dormire di meno. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
     }else{
        if ($hours == 1) {
         $result = "No, dormi abbastanza. In media dormi " .$hours. " ora e " . $minutes . " minuti";
        }else{
          $result = "No, dormi abbastanza. In media dormi " .$hours. " ore e " . $minutes . " minuti";
        }
    }
  }elseif (strpos($text, 'di più')) {

     if($asleepAV >= 390){
      if ($hours == 1) {
       $result = "No, non dovresti dormire di più. In media dormi " .$hours. " ora e " . $minutes . " minuti";
      }else{
        $result = "No, non dovresti dormire di più. In media dormi " .$hours. " ore e " . $minutes . " minuti";
      }
     }else{
      if ($hours == 1) {
        $result = "Si, dovresti dormire di più. In media dormi " .$hours. " ora e " . $minutes . " minuti";
      }else{
        $result = "Si, dovresti dormire di più. In media dormi " .$hours. " ore e " . $minutes . " minuti";
      }
    }
    
  }elseif (strpos($text, 'poco')){
    
     if($asleepAV >= 390){
      if ($hours == 1) {
       $result = "No, dormi abbastanza. In media dormi " .$hours. " ora e " . $minutes . " minuti";
      }else{
        $result = "No, dormi abbastanza. In media dormi " .$hours. " ore e " . $minutes . " minuti";
      }
     }else{
      if ($hours == 1) {
       $result = "Si, dovresti dormire di più. In media dormi " .$hours. " ora e " . $minutes . " minuti";
      }else{
       $result = "Si, dovresti dormire di più. In media dormi " .$hours. " ora e " . $minutes . " minuti";
      }
     }

  }else{
    if ($hours == 1) {
      $result = "In media dormi " .$hours. " ora e " . $minutes . " minuti";;
    }else{
      $result = "In media dormi " .$hours. " ore e " . $minutes . " minuti";;
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
 usando dei valori soglia (390 minuti di sonno) per
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
      return "Non sono riuscito a recuperare i dati relativi al tuo sonno &#x1F62D; Controlla se sono presenti nel tuo profilo!";
    }

    if($result['minutesAsleep'] != null){
      $data = $data2;
      $minutesAsleep = $result['minutesAsleep'];
      $timeinbed = $result['timeInBed'];

    }else{
      return "Non sono riuscito a recuperare i dati relativi al tuo sonno &#x1F62D; Controlla se sono presenti nel tuo profilo!";
    }
  }

  //Conversione minuti in ore e minuti
  if ($minutesAsleep < 1) {
    return "Non mi risulta che tu abbia dormito &#x1F631;";
  }
  $hours = floor($minutesAsleep / 60);
  $minutes = ($minutesAsleep % 60);


  if(strpos($text, 'abbastanza') || strpos($text, 'bene')){

    if($minutesAsleep >= 390 ){
       
       if($flag == true){
          if ($hours == 1) {
            $answer = "Si, hai dormito abbastanza. Hai dormito per ben ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "Si, hai dormito abbastanza. Hai dormito per ben ".$hours. " ore e " . $minutes . " minuti";
          }
       }else{
          if ($hours == 1) {
            $answer ="Gli ultimi in mio possesso risalgono al ".$data." ed hai dormito abbastanza. Ovvero " .$hours. " ora e " . $minutes . " minuti"; 
          }else{
              $answer ="Gli ultimi in mio possesso risalgono al ".$data." ed hai dormito abbastanza. Ovvero ".$hours. " ore e " . $minutes . " minuti";   
          }
          
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, non hai dormito abbastanza. Hai dormito solo per ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "No, non hai dormito abbastanza. Hai dormito solo per ".$hours. " ore e " . $minutes . " minuti";
          }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e vedo che non hai dormito abbastanza. Infatti solo per  "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e vedo che non hai dormito abbastanza. Infatti solo per  "
          .$hours. " ore e " . $minutes . " minuti";
        }

       }

    }
/*
  }

  elseif( strpos($text, 'bene')){

      if($minutesAsleep >= 390 ){
       
       if($flag == true){
          if ($hours == 1) {
            $answer = "Si, hai dormito bene. Hai dormito ben ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "Si, hai dormito bene. Hai dormito ben ".$hours. " ore e " . $minutes . " minuti";
          }
          
       }else{
        if ($hours == 1) {
            $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che hai dormito bene ovvero per ben "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
            $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che hai dormito bene ovvero per ben "
          .$hours. " ore e " . $minutes . " minuti";
        }
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, non hai dormito bene. Hai dormito solo per ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "No, non hai dormito bene. Hai dormito solo per ".$hours. " ore e " . $minutes . " minuti";
          }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e non hai dormito molto bene. Infatti hai dormito solo per "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e non hai dormito molto bene. Infatti hai dormito solo per "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }

    }
*/
  }elseif (strpos($text, 'tanto')) {

      if($minutesAsleep >= 390 ){
       
       if($flag == true){
        if ($hours == 1) {
          $answer = "Si, hai dormito tanto. Hai dormito per ben ".$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer = "Si, hai dormito tanto. Hai dormito per ben ".$hours. " ore e " . $minutes . " minuti";
        }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che hai dormito tanto. Ovvero per "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che hai dormito tanto. Ovvero per "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, non hai dormito tanto. Hai dormito solo per ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "No, non hai dormito tanto. Hai dormito solo per ".$hours. " ore e " . $minutes . " minuti";
          }
          
       }else{
          if ($hours == 1) {
              $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che non hai dormito tanto. Solo "
            .$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che non hai dormito tanto. Solo "
            .$hours. " ore e " . $minutes . " minuti";
          }
          
       }

    }
    
  }

  elseif(strpos($text, 'meno')){

      if($minutesAsleep >= 480 ){
       
       if($flag == true){
        if ($hours == 1) {
          $answer = "Si, dovresti dormire di meno. Vedo che hai dormito per ".$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer = "Si, dovresti dormire di meno. Vedo che hai dormito per ".$hours. " ore e " . $minutes . " minuti";
        }
          
       }else{
        if ($hours == 1) {
            $answer ="Gli ultimi in mio possesso risalgono a ".$data." e noto che dovresti dormire di meno. Hai dormito per "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono a ".$data." e noto che dovresti dormire di meno. Hai dormito per "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "No, non hai dormito abbastanza. Hai dormito solamente per ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "No, non hai dormito abbastanza. Hai dormito solamente per ".$hours. " ore e " . $minutes . " minuti";
          }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che non hai dormito abbastanza. Solo "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che non hai dormito abbastanza. Solo "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }

    }

  }elseif(strpos($text,'di più')){

        if($minutesAsleep >= 390 ){
       
       if($flag == true){
        if ($hours == 1) {
          $answer = "No, non dovresti dormire di più perchè hai dormito per ".$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer = "No, non dovresti dormire di più perchè hai dormito per ".$hours. " ore e " . $minutes . " minuti";
        }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che non dovresti dormire di più visto che hai dormito "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che non dovresti dormire di più visto che hai dormito "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }
       
    }else{
        if($flag == true){
          if ($hours == 1) {
            $answer = "Si, dovresti dormire di più. Infatti hai dormito ".$hours. " ora e " . $minutes . " minuti";
          }else{
            $answer = "Si, dovresti dormire di più. Infatti hai dormito ".$hours. " ore e " . $minutes . " minuti";
          }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che dovresti dormire di più visto che hai dormito solamente per "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che dovresti dormire di più visto che hai dormito solamente per "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }

    }

  }elseif (strpos($text,'poco')) {

    if($minutesAsleep >= 390 ){
       
       if($flag == true){
        if ($hours == 1) {
          $answer = "No, hai dormito abbastanza. Infatti hai dormito ".$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer = "No, hai dormito abbastanza. Infatti hai dormito ".$hours. " ore e " . $minutes . " minuti";
        }
          
       }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che hai dormito abbastanza ovvero "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che hai dormito abbastanza ovvero "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
       }
       
    }else{
      if($flag == true){
        if ($hours == 1) {
          $answer = "Si, dovresti dormire di più. Hai dormito ".$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer = "Si, dovresti dormire di più. Hai dormito ".$hours. " ore e " . $minutes . " minuti";
        }
          
      }else{
        if ($hours == 1) {
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che dovresti dormire di più. Hai dormito solamente "
          .$hours. " ora e " . $minutes . " minuti";
        }else{
          $answer ="Gli ultimi in mio possesso risalgono al ".$data." e noto che dovresti dormire di più. Hai dormito solamente "
          .$hours. " ore e " . $minutes . " minuti";
        }
          
      }
    }

  }else{

      //Conversione minuti in ore e minuti
      if ($minutesAsleep < 1) {
        return "Non mi risulta che tu abbia dormito &#x1F631;";
      }
      $hours = floor($minutesAsleep / 60);
      $minutes = ($minutesAsleep % 60);

      if ($hours == 1) {
          $answer = "Hai dormito ". $hours ." ora e " .$minutes . ' minuti'; 
      }else{
          $answer = "Hai dormito ". $hours ." ore e " .$minutes . ' minuti';
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

    //Conversione minuti in ore e minuti
    $hoursSleep = floor($minutesAsleep / 60);
    $minutesSleep = ($minutesAsleep % 60);

    //Conversione minuti in ore e minuti
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
      return "Non sono riuscito a recuperare i dati relativi al tuo sonno &#x1F62D; Controlla se sono presenti nel tuo profilo!";
    }



    $answer = "Gli ultimi dati in mio possesso sono relativi al ".$data2."<br>";

    if($result['minutesAsleep'] != null){
        $answer .= $resp;

       $minutesAsleep = $result['minutesAsleep'];
       $timeinbed = $result['timeInBed'];

      //Conversione minuti in ore e minuti
      $hoursSleep = floor($minutesAsleep / 60);
      $minutesSleep = ($minutesAsleep % 60);

      //Conversione minuti in ore e minuti
      $hoursBed = floor($timeinbed / 60);
      $minutesBed = ($timeinbed % 60);

     $answer = str_replace("X1",$hoursSleep,$answer);
     $answer = str_replace('X2', $minutesSleep, $answer);
     $answer = str_replace("Y1",$hoursBed,$answer);
     $answer = str_replace('Y2', $minutesBed, $answer);

    }else{
      $answer = "Non sono riuscito a recuperare i dati relativi al periodo che mi hai indicato &#x1F62D;";
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
 chiama quindi il metodo fetchYesterdaySleep per ottenere i minuti di 
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
        $result = "dal ".$startDate ." al ".$endDate;

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

  //Conversione minuti in ore e minuti
  $hoursSleep = floor($asleepAV / 60);
  $minutesSleep = ($asleepAV % 60);

  //Conversione minuti in ore e minuti
  $hoursBed = floor($inBedAV / 60);
  $minutesBed = ($inBedAV % 60);

  $result .= " in media hai dormito ".$hoursSleep ." ore e " .$minutesSleep ." minuti, trascorrendo nel letto ".$hoursBed." ore e " .$minutesSleep ." minuti";

  return $result;


}







