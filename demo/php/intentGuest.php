<?php
namespace Google\Cloud\Samples\Dialogflow;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Guzzle\Http\Exception\ClientErrorResponseException;

require __DIR__.'/vendor/autoload.php';


include 'SpotifyIntent.php';
include 'Video.php';
include 'News.php';
include 'Meteo.php';
include 'Food.php';

include 'Workout.php';

include 'Tv.php';

//require './Workout.php';
//include 'GetValuesFunctions.php';
//include 'FetchWorkoutFunctions.php';

//require($_SERVER['DOCUMENT_ROOT'].'/php/WorkoutRecSys/Workout.php');
//require($_SERVER['DOCUMENT_ROOT'].'/php/WorkoutRecSys/GetValuesFunctions.php');
//require($_SERVER['DOCUMENT_ROOT'].'/php/WorkoutRecSys/FetchWorkoutFunctions.php');

$city = "Roma";
header('Content-type: text/plain; charset=utf-8');
 ini_set('display_errors', 1);
//Controllo se la variabile 'testo' ricevuta è nulla
if (isset($_POST{'testo'})) {
    $testo = $_POST{'testo'};
}

if(isset($_POST{'city'})){
    $city = $_POST{'city'};
}

//Email utente anonimo
$email = "UtenteAnonimo";


function detect_intent_texts($projectId,$city,$email, $text, $sessionId, $languageCode = 'it-IT')
{
    // new session
    $test = array('credentials' => 'myrrorbot-4f360-cbcab170b890.json');
    $sessionsClient = new SessionsClient($test);
    $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());
    
    // create text input
    $textInput = new TextInput();
    $textInput->setText($text);
    $textInput->setLanguageCode($languageCode);

    // create query input
    $queryInput = new QueryInput();
    $queryInput->setText($textInput);
    
    // get response and relevant info
    $response = $sessionsClient->detectIntent($session, $queryInput);
    $queryResult = $response->getQueryResult();
 

    $parameters=json_decode($queryResult->getParameters()->serializeToJsonString(), true);
    
    $queryText = $queryResult->getQueryText();
    $intent = $queryResult->getIntent();
    
    //risposta intent
    $fulfilmentText = $queryResult->getFulfillmentText();

    if(!is_null($intent)){
        $displayName = $intent->getDisplayName(); //Nome dell'intent
        $confidence = $queryResult->getIntentDetectionConfidence(); //Livello di confidence
        selectIntent($email,$displayName,$confidence,$text,$fulfilmentText,$parameters,$city);
        
    }else{

        $answer = "Purtroppo non ho capito la domanda. Prova a rifarla con altre parole! Devo ancora imparare molte cose &#x1F605;";

        //Stampo la risposta relativa all'intent non identificato
        $arr = array('intentName' => "Non identificato", 'confidence' => "0",'answer' => $answer);
        printf(json_encode($arr,'UTF8'));  //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8

    }
    
    $sessionsClient->close();
}



function selectIntent($email,$intent, $confidence,$text,$resp,$parameters,$city){

    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.60){              

        $answer = null;

        switch ($intent) {

            case 'Meteo citta':
        
            $answer = getCityWeather($parameters,$text);
            break;

            case 'meteo binario':
             //$city = "Bari";
                $answer = binaryWeather($city,$parameters,$text);
                break; 

            case 'Meteo':
                //Per sperimentazione, Bari o Milano, random
                $n = rand(0,1);
                switch ($n) {
                    case '0':
                        $city = 'Bari';
                        break;
                    
                    default:
                        $city = 'Milano';
                        break;
                }
                //$city = "Bari";
                $answer = getWeather($city,$parameters,$text,null);
                break;

            case 'attiva debug':
                $answer = $resp;
                break;

            case 'disattiva debug':
                $answer = $resp;
                break;

            case 'Default Welcome Intent':
                $answer = $resp;
                break;

            case 'Allenamento generico':
                $answer = retriveWorkout($resp, $parameters, $text, null);
                break;

            case 'Allenamento personalizzato':
                $answer = recommendWorkoutGuest($resp, $parameters, $text, null);
                break;    


            case 'Ritrovamento programma':
                $answer = retriveTV($resp,$parameters,$text,null);
                break;

            case 'Raccomandazione programma':
                $answer = recommendTVGuest($resp,$parameters,$text,null);
                break;    
                       

            default:
               
                    $answer = "Questa funzione è disponibile solo dopo aver effettuato il login a myrror";

               
                break;
        }

    }else {
            $answer = "Questa funzione è disponibile solo dopo aver effettuato il login a myrror ";

    }


     //SPOTIFY --> Valori soglia diversi
    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50 && ($intent == 'Musica')){

        $answer = getMusicGuest($resp,$parameters,$text,$email);
    }

    //YOUTUBE --> Valori soglia diversi
    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50 && ( $intent == 'Ricerca Video'     )){
        switch ($intent) {

            case 'Ricerca Video':
                $answer = getVideoBySearch($resp,$parameters,$text,$email);
                break;

            default:
                  $answer = "Questa funzione è disponibile solo dopo aver effettuato il login a myrror ";

                break;
        }
    }
    
    //GOOGLE-NEWS--> Valori soglia diversi
    if($confidence >= 0.50  && ($intent == 'News')){

        $answer = getNews($parameters,'UtenteAnonimo',$text);

    }
    
    //CIBO
    if($intent == 'Cibo'){
        $listaHealthy = array(' salutare', ' sana', ' sano');
        $listaLight = array(' leggera', ' light', ' leggero');
        $listaParoleIngredient = array(' a base di ', ' con ', 'contenente ');
        $flagHealthy = false;
        $flagLight = true;
        $flagVeg = false;
        $flagLac = false;
        $flagNick = false;
        $flagGluten = false;
        $ingredient = "";

        
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
       
        //Controllo se sono presenti le parole della lista ingredienti allora setto a vero il flag ingredienti
        /*
        foreach($listaParoleIngredient as $parola)  {  
            if (stripos($text, $parola) !== false) {
                //Contiene la parola
                $ingredient = explode($parola, $text)[1];
                break;
            } 
        }*/

        //pre sperimentazione
       //$answer = getRecipeByIngredient($resp,$parameters,$text,$email, 'pesce', $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);

        if($flagHealthy == false && $flagNick == false && $flagLight == false && $flagVeg == false && $flagLac == false && $flagGluten == false){
           /* 
           Se sono tutti false, per la simulazione, gli avvaloriamo randomicamente.

           flagHealthy = 0
           flagNick = 1
           flagLight = 2
           flagVeg = 3
           flagLac = 4 
           flagGluten = 5
           
           */ 
         //  echo"tutti a false";

           $intero = rand(0,5);
          // echo$intero;

           switch ($intero) {
               case 0:
                   $flagHealthy =true;
                   break;

                case 1:
                    $flagNick = true;
                    break;

                case 2:
                    $flagLight = true;
                    break;

                case 3:
                    $flagVeg = true;
                    break;

                 case 4:
                    $flagLac = true;
                    break;

                 case 5:
                    $flagGluten = true;  
                    break;
               
           }



        }

        //print("healty")

        //$answer = getStandardRecipe($resp,$parameters,$text,$email,true, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
        $answer = getStandardRecipe($resp,$parameters,$text,$email, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);
       // $answer = getRecipeSperimentazione($resp,$parameters,$text,$email);

        //print_r($answer);

    }

    //Stampo la risposta
    $arr = array('intentName' => $intent, 'confidence' => $confidence,'answer' => $answer);

  

    if ($arr['intentName'] == 'Canzone per nome') {
        printf(json_encode($arr)); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
    }else{
    //printf(json_encode($arr))
      printf(json_encode($arr , JSON_UNESCAPED_UNICODE)); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
    //echo json_encode($arr);
  }
    
}

//date_default_timezone_set('Europe/Madrid'); //Imposto la stessa timezone di Dialogflow (per gli orari)

try {
  detect_intent_texts('myrrorbot-4f360',$city,"",$testo,'123456');
} catch (ClientErrorResponseException $exception) {
    $responseBody = $exception->getResponse()->getBody(true);
    echo $responseBody;
}



