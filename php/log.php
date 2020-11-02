<?php


if (isset($_POST['answer']) && isset($_POST['rating']) && isset($_POST['quest']) && isset($_POST['email']) 
  && isset($_POST['timestampStart']) && isset($_POST['timestampEnd'])) {
	
	 // $answer = $_POST['answer'];

$answer = get_magic_quotes_gpc() ?
    stripslashes($_POST['answer']) : $_POST['answer'];

    //$answer = str_replace( "'",0);
    //print_r($answer);
    $arr = json_decode($answer,true);
    //print_r($arr);
    //var_dump($arr);
    $intent = $arr['intentName'];
    $confidence = $arr['confidence'];
    $ans = $arr['answer'];


   
    $rate = $_POST['rating'];
    $quest = $_POST['quest'];
    $email = $_POST['email'];
    
    //$timestamp = time();

    $timestampStart = $_POST['timestampStart'];
    $timestampEnd = $_POST['timestampEnd'];

    $file = fopen("logging.txt", "a") or die("Unable to open file!");

    //fwrite($file, "time: ".$timestamp."\r\n");
    fwrite($file, "user: ".$email."\r\n");

    fwrite($file, "timestampStart: ".$timestampStart."\r\n");
    fwrite($file, "timestampEnd: ".$timestampEnd."\r\n");

    $string = "message: ".$quest."\r\n";
    fwrite($file, $string);
    fwrite($file, "intent: ".$intent."\r\n");
    fwrite($file,"confidence: ". $confidence."\r\n");
     fwrite($file,"answer: ");
    if(is_array($ans)){
           foreach ($ans as $key => $value) {
        fwrite($file, $key.": ".$value."\r\n");
    }
}else{
     fwrite($file,$ans);
      fwrite($file, "\r\n");
}
   

 
    //fwrite($file, serialize($ans));
   
    
    if(isset($_POST['flag'])){
      $flag = $_POST['flag'];
      fwrite($file,"click: ". $flag."\r\n");
    }

    $current = "feedback:".$rate."\r\n\r\n";

    //Write the contents back to the file
    //file_put_contents($file, $current);
    fwrite($file, $current);
    fclose($file);
}





?>