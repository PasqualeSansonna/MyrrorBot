<?php
namespace Google\Cloud\Samples\Dialogflow;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Guzzle\Http\Exception\ClientErrorResponseException;

require __DIR__.'/vendor/autoload.php';

include "readLocaljson.php";
include 'Behaviors.php';
include "PhysicalState.php";
include "Affects.php";
include 'Demographics.php';
include 'Interests.php';
include 'SocialRelations.php';
include 'CognitiveAspects.php';
include 'SpotifyIntent.php';
include 'Video.php';
include 'News.php';
include 'Meteo.php';
include 'Food.php';
include 'Workout.php';

include 'GetValuesFunctions.php';

include 'Tv.php';



$city = "Bari";
header('Content-type: text/plain; charset=utf-8');
 ini_set('display_errors', 1);
//Controllo se la variabile 'testo' ricevuta è nulla
if (isset($_POST{'testo'})) {
    $testo = $_POST{'testo'};
    if( stripos($testo, 'weekend') !== false  
        && stripos($testo, 'prossimo') == false ){
      $pos = stripos($testo, 'weekend');
  $testo = substr_replace($testo, ' prossimo ', $pos, 0);

    }elseif ( stripos($testo, 'fine settimana') !== false  && stripos($testo, 'prossimo') == false) {
        $pos = stripos($testo, 'fine settimana');
        $testo = substr_replace($testo, ' prossimo ', $pos, 0);
    }
}

if(isset($_POST{'city'})){
    $city = $_POST{'city'};
}

if(isset($_POST{'mail'})){
    $email = $_POST{'mail'};
}

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
        printf(json_encode($arr,JSON_UNESCAPED_UNICODE));  //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8



    }
    
    $sessionsClient->close();
}



function selectIntent($email,$intent, $confidence,$text,$resp,$parameters,$city){

    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50){              

        $answer = null;

        switch ($intent) {

            case 'Attivita fisica':
                $answer = attivitaFisica($resp,$parameters,$text,$email);
                break;

            case 'Attivita fisica binario':
				print_r($parameters);
                $answer = attivitaFisicaBinary($resp,$parameters,$text,$email);
                break;
    
            case 'Battito cardiaco':
                $answer= getCardio($resp,$parameters,$text,$email);
                break;

            case 'Battito cardiaco binario':
                $answer= getCardioBinary($resp,$parameters,$text,$email);
                break;
     
            case 'Calorie bruciate':
                $answer = getCalories($resp,$parameters,$text,$email);
                break;

            case 'Calorie bruciate binario':
                $answer = getCaloriesBinary($resp,$parameters,$text,$email);
                break;

            case 'Contapassi':
                $answer = getSteps($resp,$parameters,$text,$email);      
                break;

            case 'Contapassi binario':
                $answer = getStepsBinary($resp,$parameters,$text,$email);      
                break;    

            case 'Contatti':
                $answer = contatti($resp,$parameters,$text,$email);
                break;

            case 'Contatti_subintent':
                $answer = contatti($resp,$parameters,$text,$email);
                break;

            case 'Email':
                $answer = email($resp,$parameters,$text,$email);
                break;
                
            case 'Peso':
                $answer = getWeight($resp,$parameters,$text,$email);
                break;

            case 'Altezza':
                $answer = getHeight($resp,$parameters,$text,$email);
                break;

            case 'Emozioni':
                $answer = getSentiment(1,$resp,$parameters,$email);
                break;

            case 'Emozioni binario':
                $answer = getSentimentBinario(1,$resp,$parameters,$email);
                break;

            case 'Umore':
                $answer = getSentiment(0,$resp,$parameters,$email);
                break;

            case 'Umore binario':
                $answer = getSentimentBinario(0,$resp,$parameters,$email);
                break;

            case 'Eta':
                $answer = getEta($resp,$parameters,$text,$email);
                break;

            case 'Identita utente':
                $answer = identitaUtente($resp,$parameters,$text,$email);
                break;

            case 'Interessi':
                $answer = interessi($resp,$parameters,$email);
                break;

            case 'Lavoro':
                $answer = lavoro($resp,$parameters,$text,$email);
                break;

            case 'Luogo di nascita':
                $answer = getCountry($resp,$parameters,$text,$email);
                break;

            case 'Ultima citta':
                $answer = Ultimacitta($email);
                break;    

            case 'Personalita':
                $answer = personalita($resp,$parameters,$email);
                break;

            case 'Personalita binario':
                $answer = personalitaBinario($resp,$parameters,$email);
                break;

            case 'Qualita del sonno':
                $answer = getSleep($resp,$parameters,$text,$email);
                break;

            case 'Qualita del sonno binario':
                $answer = getSleepBinary($resp,$parameters,$text,$email);
                break;

            case 'Sedentarieta':
                $answer = getSedentary($resp,$parameters,$text,$email);
                break;

            case 'Sedentarieta binario':
                $answer = getSedentaryBinary($resp,$parameters,$text,$email);
                break;
                
             case 'meteo binario':
             //$city = "Bari";
                $answer = binaryWeather($city,$parameters,$text);
                break; 

             case 'Meteo odierno':
             //$city = "Bari";
                $answer = getTodayWeather($city,$parameters,$text);
                break; 

            case 'Meteo citta':
            
                $answer = getCityWeather($parameters,$text);
                break;

             case 'Meteo':
               //$city = "Bari";
                $answer = getWeather($city,$parameters,$text,$email); //email aggiunta per sperimentazione
                break;

            case 'attiva debug':
                $answer = $resp;
                break;

            case 'disattiva debug':
                $answer = $resp;
                break;

            case 'Allenamento personalizzato':
                 $answer = recommendWorkout($resp, $parameters, $text, $email);
                 break;
             
            case 'Allenamento generico':
                 $answer = retriveWorkout($resp, $parameters, $text, $email);
                 break;

            case 'Ritrovamento programma':
            	$answer = retriveTV($resp,$parameters,$text,$email);
            	break;

            case 'Raccomandazione programma':
            	$answer = recommendTV($resp,$parameters,$text,$email);
            	break;         
                
			
            default:
                if ($resp != "") { //Small Talk
                    $answer = $resp;
                }else{
                    $answer = "Purtroppo non ho capito la domanda. Prova a rifarla con altre parole! Devo ancora imparare molte cose &#x1F605;";

                }
                break;
        }

    }else {
        $answer = "Purtroppo non ho capito la domanda. Prova a rifarla con altre parole! Devo ancora imparare molte cose &#x1F605;";
    }


    //SPOTIFY --> Valori soglia diversi
    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50 && ($intent == 'Musica')){

        $answer = getMusic($resp,$parameters,$text,$email);
    }


    //YOUTUBE --> Valori soglia diversi
    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50 && ($intent == 'Video in base alle emozioni' || $intent == 'Ricerca Video'  || $intent == 'Video in base alle emozioni subintent'   )){
        switch ($intent) {
            case 'Video in base alle emozioni':
                $answer = getVideoByEmotion($resp,$parameters,$text,$email);
                break;

            case 'Ricerca Video':
                $answer = getVideoBySearch($resp,$parameters,$text,$email);
                break;

            default:
                $answer = "Purtroppo non ho capito la domanda. Prova a rifarla con altre parole! Devo ancora imparare molte cose &#x1F605;";
                break;
        }
    }
    
    //GOOGLE-NEWS--> Valori soglia diversi
    if($confidence >= 0.50  && ($intent == 'News')){

        $answer = getNews($parameters,$email,$text);

    }
	
//CIBO
if($intent == 'Cibo'){

        $answer = getRecipe($resp,$parameters,$text,$email);

    }

    //Stampo la risposta
    $arr = array('intentName' => $intent, 'confidence' => $confidence,'answer' => $answer);

    if ($arr['intentName'] == 'Canzone per nome') {
        printf(json_encode($arr)); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8


    }else{
     echo json_encode($arr); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
       // printf(json_encode($arr,JSON_UNESCAPED_UNICODE)); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
    }

}

//date_default_timezone_set('Europe/Madrid'); //Imposto la stessa timezone di Dialogflow (per gli orari)

try {
  detect_intent_texts('myrrorbot-4f360',$city,$email,$testo,'123456');
} catch (ClientErrorResponseException $exception) {
    $responseBody = $exception->getResponse()->getBody(true);
    echo $responseBody;
}



