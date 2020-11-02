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


function detect_intent_texts($projectId,$city,$email, $text, $sessionId, $languageCode = 'en-UK')
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

        $answer = "Unfortunally I didn't understand the question.Try with different words.I still have to learn a lot of things &#x1F605;";

        //Stampo la risposta relativa all'intent non identificato
        $arr = array('intentName' => "Non identificato", 'confidence' => "0",'answer' => $answer);
        //printf(json_encode($arr,'UTF8'));  //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
	printf(json_encode($arr,JSON_UNESCAPED_UNICODE));  //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8


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
               //$city = "Bari";
                $answer = getWeather($city,$parameters,$text);
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

            case 'Ritrovamento programma':
                $answer = retriveTV($resp,$parameters,$text,null);
                break;        

            default:
               
                    $answer = "You can access to this function after login only";

               
                break;
        }

    }else {
            $answer = "You can access to this function after login only";

    }


     //SPOTIFY --> Valori soglia diversi
    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50 && ($intent == 'Musica')){

        $answer = getMusic($resp,$parameters,$text,$email);
    }

    //YOUTUBE --> Valori soglia diversi
    if(($confidence > 0.86 ||  str_word_count($text) >= 2) && $confidence >= 0.50 && ( $intent == 'Ricerca Video'     )){
        switch ($intent) {

            case 'Ricerca Video':
                $answer = getVideoBySearch($resp,$parameters,$text,$email);
                break;

            default:
                  $answer = "You can access to this function after login only";

                break;
        }
    }
    
    //GOOGLE-NEWS--> Valori soglia diversi
    if($confidence >= 0.50  && ($intent == 'News')){

        $answer = getNews($parameters,$email,$text);

    }
    
    //CIBO
    if($intent == 'Cibo'){
        $listaHealthy = array(' healthy');
        $listaLight = array(' light'); 
        $listaParoleIngredient = array(' of ', ' containing ', ' with ');
        $flagHealthy = false;
        $flagLight = false;
        $ingredient = "";
	$flagVeg = false;
	$flagLac = false;
	$flagNick = false;
	$flagGluten = false;
        
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
        foreach($listaParoleIngredient as $parola)  {  
            if (stripos($text, $parola) !== false) {
                //Contiene la parola
                $ingredient = explode($parola, $text)[1];
                break;
            } 
        }
        
        //$answer = getRecipeByIngredient($resp,$parameters,$text,$email, $ingredient, $flagHealthy, $flagLight);
	$answer = getRecipeByIngredient($resp,$parameters,$text,$email, $ingredient, $flagHealthy, $flagLight, $flagVeg, $flagLac, $flagNick, $flagGluten);

    }

    //Stampo la risposta
    $arr = array('intentName' => $intent, 'confidence' => $confidence,'answer' => $answer);

    //print_r($answer);

    if ($arr['intentName'] == 'Canzone per nome') {
        printf("%s",json_encode($arr)); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
    }else{
	//print_r($arr);
        printf("%s",json_encode($arr,JSON_UNESCAPED_UNICODE)); //JSON_UNESCAPED_UNICODE utilizzato per il formato UTF8
    }
}

//date_default_timezone_set('Europe/Madrid'); //Imposto la stessa timezone di Dialogflow (per gli orari)

try {
  detect_intent_texts('myrrorbot-4f360',$city,"",$testo,'123456');
} catch (ClientErrorResponseException $exception) {
    $responseBody = $exception->getResponse()->getBody(true);
    echo $responseBody;
}



