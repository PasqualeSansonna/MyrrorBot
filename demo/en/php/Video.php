  <?php
    require_once "Affects.php";
    require_once "readLocaljson.php";

    // Call set_include_path() as needed to point to your client library.
    require_once ('google-api-php-client/src/Google_Client.php');
    require_once ('google-api-php-client/src/contrib/Google_YouTubeService.php');


function explainVideo($email){

  $emotion = getLastEmotion($email);
  $answer = "I recommended you this video because ";
    switch ($emotion) {
      case 'gioia':
        $answer .= "you're happy  &#x1f601";
        break;

      case 'paura':
       $answer .= "you're scared &#x1f628";
        break;

      case 'rabbia':
        $answer .= "you're angry &#x1f621";
        break;

      case 'disgusto':
        $answer .= "you're disgusted &#x1f629";
        break;

      case 'tristezza':
       $answer .= "you're sad &#x1f625";
        break;

      case 'sorpresa':
        $answer .= "you're amazed &#x1f631";
        break;
      
      default:
       $answer .= "you're mood is neutral  &#x1f636";
        break;
    }

    return $answer;
}

  function getVideoByEmotion($resp,$parameters,$text,$email){
    $DEVELOPER_KEY = "AIzaSyADh47AR3xdQPMT0oXTPJatZQ_Cbhw9YhM";

    $videos = array();
    $emotion = getLastEmotion($email);
    $q = "";
    $searchResponse = "";

    $client = new Google_Client();
    $client->setDeveloperKey($DEVELOPER_KEY);
    $htmlBody = '';
    $youtube = new Google_YoutubeService($client);

    try {
    switch ($emotion) {
      case 'gioia':
        $q = "funny videos";
        break;

      case 'paura':
        $q = "motivational videos";
        break;

      case 'rabbia':
        $q = "relaxing videos";
        break;

      case 'disgusto':
        $q = "funny videos";
        break;

      case 'tristezza':
        $q = "entertaining videos";
        break;

      case 'sorpresa':
        $q = "news videos";
        break;
      
      default:
        $q = "news videos";
        break;
    }


      if( $searchResponse == ""){
          $searchResponse = $youtube->search->listSearch('id,snippet', array(
          'q' => $q,
          'type' => 'video',
          'maxResults' => 10
        ));
      }
      

         foreach ($searchResponse['items'] as $searchResult) {
          switch ($searchResult['id']['kind']) {
            case 'youtube#video':
             array_push($videos, "http://www.youtube.com/embed/".$searchResult['id']['videoId']);
              break;
          
           }
        }

       $ind = rand(0,sizeof($videos) -1);
       $spiegazione = explainVideo($email);
  
      return array("ind" => $videos[$ind], "explain" => $spiegazione);
      
        
      } catch (Google_ServiceException $e) {
        $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
      } catch (Google_Exception $e) {
        $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
      }
  
       return $htmlBody;

  }




  function getVideoBySearch($resp,$parameters,$text,$email){
    $DEVELOPER_KEY = "AIzaSyADh47AR3xdQPMT0oXTPJatZQ_Cbhw9YhM";

    $videos = array();
    $emotion = getLastEmotion($email);
    $q = "";
    $searchResponse = "";

    $client = new Google_Client();
    $client->setDeveloperKey($DEVELOPER_KEY);
    $htmlBody = '';
    $youtube = new Google_YoutubeService($client);

   
    //Verifico se è presente il genere del brano
    if ($parameters['any'] != "") {
      $q = $parameters['any']; //Genere del brano
    }else{
      $q = "";
      return "I didn't recognize the phrase. Try again!";
    }

      if( $searchResponse == ""){
          $searchResponse = $youtube->search->listSearch('id,snippet', array(
          'q' => $q,
          'type' => 'video',
          'maxResults' => 10
        ));
      }
      

         foreach ($searchResponse['items'] as $searchResult) {
          switch ($searchResult['id']['kind']) {
            case 'youtube#video':
             array_push($videos, "http://www.youtube.com/embed/".$searchResult['id']['videoId']);
              break;
          
           }
        }

       $ind = rand(0,sizeof($videos) -1);
       $spiegazione = explainVideo($email);
  
      return array("ind" => $videos[$ind], "explain" => "");


  }

?>