<?php
/*
Parameters contiene gli argomenti restituiti da dialogflow
Tramite parameters controlliamo quale sia il parametro individuato
da dialogflow e costruiamo di conseguenza il link di ricerca in 
base alla categoria.
Se viene trovato almeno un articolo della categoria scelta il suo
URL verrà restituito , altrimenti viene restituito un messaggio d'errore
*/
function getNewsTopic($parameters){
$arr  = array('','','');
$list = array();
$link = "https://newsapi.org/v2/top-headlines?country=us&category=";
$apiKey = "&apiKey=17c1953c3cc7450d958ff14f9e262c02";
$val = "";

if ($parameters['Sports'] != null) {
	$val = $parameters['Sports'];
	$link .= "Sports&q=".$val. $apiKey;
}elseif ($parameters['Health'] != null) {
	$val = $parameters['Health'];
	$link .= "Health&q=".$val. $apiKey;
}elseif ($parameters['Science'] != null) {
	$val = $parameters['Science'];
	$link .= "Science".$apiKey;
}elseif ($parameters['Entertainment'] != null) {
	$val = $parameters['Entertainment'];
	$link .= "Entertainment&q=".$val. $apiKey;
}elseif ($parameters['Technology'] != null) {
	$val = $parameters['Technology'];
	$link .= "Technology".$apiKey;
}elseif ($parameters['Business'] != null) {
	$val = $parameters['Business'];
	$link .= "Business&q=".$val. $apiKey;
}else{
	return "";
}



$json = googleNewsQuery($link);
$url = "";

if(!isset($json['articles'] ))
   return "";

foreach ($json['articles'] as $key => $value) {
	$url = $value['url'];
	$image = $value['urlToImage'];
	$title = $value['title'];
   if($url != ""){
     $arr[0] = $url;
	 $arr[1] = $image;
	 $arr[2] = $title;
	 array_push($list, $arr);
	
    }

	    if (count($list) == 30) {
    	$i = rand(0,9);
    	$arr = $list[$i];
    	return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
        }
}

if(count($list) == 0){
$every= "https://newsapi.org/v2/everything?q=".$val
."&language=en&sortBy=publishedAt&apiKey=17c1953c3cc7450d958ff14f9e262c02";
$json = googleNewsQuery($every);
foreach ($json['articles'] as $key => $value) {
	$url = $value['url'];
	$image = $value['urlToImage'];
	$title = $value['title'];
      if($url != ""){
     $arr[0] = $url;
	 $arr[1] = $image;
	 $arr[2] = $title;
	 array_push($list, $arr);
	
    }

	    if (count($list) == 30) {
    	$i = rand(0,9);
    	$arr = $list[$i];
    	return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
        }
}

if($url == "")
	return "";

}else{
	$i = rand(0,count($list)-1);
    $arr = $list[$i];
    	return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
}

}
/*
il metodo chiama getInterestsList per ottenere la lista
dei 30 maggiori interessi dell'utente , per ogni interesse 
effettua una ricerca tramite googleNewsQuery fin quando 
non viene trovato un articolo,una volta trovato l'articolo
viene restituito l'url altrimenti un messaggio d'errore
*/

function getInterestsNews($email){

$arr  = array('','','','');
$articles = array();
$list = getInterestsList($email);
//echo $list[0];
foreach ($list as $key => $value){

	$link = "https://newsapi.org/v2/everything?q=".$key."&sortBy=publishedAt&apiKey=17c1953c3cc7450d958ff14f9e262c02";
	$link = str_replace(' ', '%20', $link);
	$json = googleNewsQuery($link);
	
	if(!isset($json['articles'] ))
        return "";


	if($json['totalResults'] != 0){
		foreach ($json['articles'] as $key2 => $value) {
			$image = $value['urlToImage'];
	        $title = $value['title'];
		    $url =  $value['url'];
	        if($url != ""){
              $arr[0] = $url;
	          $arr[1] = $image;
	          $arr[2] = $title;
	          $arr[3] = "I recommended you this article beacause you're interested in ".$key;
	          array_push($articles, $arr);

            }
		    if (count($articles) == 10) {
    	       $i = rand(0,9);
    	       $arr = $articles[$i];
        	   return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2],'explain' => $arr[3] );
            }
		}
		
	}
if(count($articles) == 0){
	return "";

}else{
	//print_r($articles);
    $i = rand(0,count($articles)-1);
    $arr = $articles[$i];
    return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2],'explain' => $arr[3] );
}

    
}

/*
se non viene trovato alcun articolo relativo agli interessi
principali dell'utente vengono restituite le notizie odierne
*/
return getTodayNews();


}

function explainNews($email){

$list = getInterestsList($email);
//echo $list[0];
foreach ($list as $key => $value){

	$link = "https://newsapi.org/v2/everything?q=".$key."&sortBy=publishedAt&apiKey=17c1953c3cc7450d958ff14f9e262c02";
	$link = str_replace(' ', '%20', $link);
	$json = googleNewsQuery($link);
	
	if(!isset($json['articles'] ))
        return "Unfurtunally I can't explain this &#x1f614";


	if($json['totalResults'] != 0){
		foreach ($json['articles'] as $key2 => $value2) {
			$image = $value2['urlToImage'];
	        $title = $value2['title'];
		    $url =  $value2['url'];

			return "I recommended you this article beacause you're interested in  ".$key ."  &#x1f600";
			
		}
		
	}
    
}

/*
se non viene trovato alcun articolo relativo agli interessi
principali dell'utente vengono restituite le notizie odierne
*/
return "I didn't found articles for you and i gave you the latest news  &#x1f600";

}

/*
Questo metodo usa googleNewsQuery($link) per ottenere
l'elenco delle maggiori notizie odierne e ne resituisce la prima
in output
*/

function getTodayNews(){

$arr  = array('','','');
$list = array();
$link = "https://newsapi.org/v2/top-headlines?country=us&apiKey=17c1953c3cc7450d958ff14f9e262c02";
$json = googleNewsQuery($link);
$url = "";

if(!isset($json['articles'] ))
   return "";

foreach ($json['articles'] as $key => $value) {
	$url = $value['url'];
	$image = $value['urlToImage'];
	$title = $value['title'];
    if($url != ""){
     $arr[0] = $url;
	 $arr[1] = $image;
	 $arr[2] = $title;
	 array_push($list, $arr);

	 
    }
	    if (count($list) == 30) {
    	$i = rand(0,9);
    	$arr = $list[$i];
    	return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
    }
}

if(count($list) == 0){
	return "";

}else{
	
    $i = rand(0,count($list)-1);
    $arr = $list[$i];
    return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
}

}
/*
@parameters parametri con i dati sul testo da cercare
la funzione prende il parametro any individuato da DIalogflow ed effettua una
ricerca tramite il metodo GoogleNewsQuery.
Se vengono trovati articoli ne viene restituito il link,
altrimenti viene restituita una notizia di oggi tramite il
metodo getTodayNews
*/
function cercaNews($parameters){

$arr  = array('','','');
$list = array();
if(isset($parameters['any'])){
$val = $parameters['any'];	
$val = str_replace(' ', '%20', $val);
$link = "https://newsapi.org/v2/everything?q=".$val.
"&sortBy=publishedAt&Language=en&apiKey=17c1953c3cc7450d958ff14f9e262c02";
$json = googleNewsQuery($link);

if(!isset($json['articles'] ))
   return "";

foreach ($json['articles'] as $key => $value) {
	$url = $value['url'];
	$image = $value['urlToImage'];
	$title = $value['title'];
	
    if($url != ""){
     $arr[0] = $url;
	 $arr[1] = $image;
	 $arr[2] = $title;
	 array_push($list, $arr);
	 
    }

    if (count($list) == 30) {
    	$i = rand(0,9);
    	$arr = $list[$i];
    	 return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
    }
}
if(count($list) == 0){
	return getTodayNews();
}else{
    $i = rand(0,count($list)-1);
    $arr = $list[$i];
    return array('link' => $link,'url' => $arr[0],'image' => $arr[1], 'title' => $arr[2] );
}

}else{
	return getTodayNews();
}

}
/*
la funzione usa il parametro $link pre fare
una chiamata REST A GOOGLE NEWS e restituisce il risultato
*/
function googleNewsQuery($link){

$ch = curl_init();
$json_data = null;

curl_setopt($ch, CURLOPT_URL,$link);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);
$result = json_decode($server_output,true);
curl_close ($ch);

return $result;

}

function getNews($parameters,$email,$text){

if ($parameters['Sports'] != null || $parameters['Health'] != null || $parameters['Science'] != null ||
 $parameters['Entertainment'] != null || $parameters['Technology'] != null || $parameters['Business'] != null ) {	
	$answer = getNewsTopic($parameters);
}elseif($parameters['any'] != null ){
    $answer = cercaNews($parameters);
}elseif (stripos($text, 'today') !== false || stripos($text, 'current') !== false || stripos($text, 'latest')  ||
 stripos($text, 'last')  !== false ){
	$answer = getTodayNews();
}elseif (stripos($text, 'interest') !== false || stripos($text, 'hint') !== false || 
	stripos($text, 'like') !== false  || stripos($text, 'recom') !== false) {

	if ($email == '') {
		return '';
	}
	$answer = getInterestsNews($email);
}else{
      $answer = getTodayNews();
}

return $answer;

}


?>