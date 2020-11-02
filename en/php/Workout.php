<?php

//include "/FetchWorkoutFunctions.php";
include 'FetchWorkoutFunctions.php';
//require ('./GetValuesFunctions.php');

//print ($_SERVER['DOCUMENT_ROOT'].'/php/GetValuesFunctions.php');
//print($_SERVER['DOCUMENT_ROOT']);

//Deve restituire una string chiamata @result che sarà poi @answer in intentdetection.php
function recommendWorkout($resp, $parameters, $text, $email)
{

	$allenamento = null; //Conterrà l'array associativo restituito dalle funzioni di ritrovamento nel file
	$urlAllenamento;
	$imgUrlAllenamento;

	$propensioneLivelloSu = false;
	$ultimoAllenamento = null;

    $param = "";
    $result = null;

    $spiegazione = "Perchè sì";

    $today = date("d-m-Y"); //oggi


    $restingHeartRate = 61;
    $sleep = 400;
    $dailyCardioMinutes = 60;
    $today = date("d-m-Y");

   // $mood = 'surprise'; //None sarebbe la neutralità. mood var usata per i test

    $mood = 'surprise';
    $bmi = 33;

    $peso = 54;
    $altezza = 174;

    // Avvaloriamo i parametri locali

    $arr = cardioToday($param, $today, $email);

    if ($arr['date'] == $today) {
    	
        $restingHeartRate = $arr['heart'];
    } else {

        $restingHeartRate = $arr['heart'];
    }

    //Cuore avvalorato

    //echo "cuore: ".$restingHeartRate;


    //Adesso avvaloriamo il sonno:

    $yesterday = date('d-m-Y',strtotime("-1 days"));
    //Prendiamo il solo sonno di ieri, se c'è, altrimenti usiamo quello di default
   $sleep = fetchYesterdaydSleep("", $yesterday ,$email);
   //$sleep = 400; 
  //echo"Il valore di Sleep e: ".$sleep;


   //Sonno avvalorato


   //Avvaloriamo i minuti del cardio per oggi,  restituisce quelli di oggi se ci sono, altrimenti quelli dell'ultima data, però noi usiamo il default se non sono quelli di oggi
  $arrayCardioMinutes =  attivitaData($today, $email);

  if($today == $arrayCardioMinutes[3]){ // l'elemento con indice 3 dell'array rappresenta la data in cui sono stati presi i minuti
  	$dailyCardioMinutes =  $arrayCardioMinutes[0] + $arrayCardioMinutes[1] + $arrayCardioMinutes[2];
  } 
  //echo"I minuti di cardio trovati in totale sono ".$dailyCardioMinutes." in data "; 


  //Minuti di cardio avvalorati, si possono aggiungere quelli sui passi se si vuole e poi
  //TODO: METTERE IL CONTROLLO SULLE CALORIE E METABOLISMO BASALE

  //Adesso avvaloriamo le emozioni: 
  //Formalmente si richiede quella di ieri, ma se non c'è , viene restituita l'ultima disponibile

  $mood = getTodayEmotion($today,$email);
//echo "Stavi provando: " .$mood ."emozione";


  //Avvaloriamo il peso
  $peso = getPeso($parameters, $email);
  //echo "Il peso e: ".$peso;


  //Avvaloriamo l'altezza
  $altezza = getAltezza($param, $email);
   //echo "L'altezza e :".$altezza;


  //Calcolo bmi
  if(!is_null($altezza) && !is_null($peso)){
       $bmi= $peso / (pow(($altezza/100),2));
  }
  //$bmi = 25.3;

//echo "Il tuo bmi e: ".$bmi;


  //$nome = getName($email);

//  echo"Il nome e': ".$nome;



  //inizio raccomandazione effettiva

  // Controlli bloccanti sul cuore
  if ($restingHeartRate < 60) {
      $spiegazione = "You can't train, your heart rate is too low.";
      return array ("explain" => $spiegazione);
  }else if($restingHeartRate > 90){
      $spiegazione = "I suggest to you this workout because your heart rate is high and i want you to lower it with cardio";
      $allenamento = fetchWorkout("Cardio",1);

      if(isset($allenamento)){
      	//Avvaloro i link
      	foreach ($allenamento as $key => $value) {
      		$urlAllenamento = $key;
      		$imgUrlAllenamento = $value;
      		break;
      	}

      }

  return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento, "explain" => $spiegazione);
  }



  //Aggiunta controllo bloccante obesità di terzo grado:
  if($bmi > 40){
    $spiegazione = "You are not supposed to use this service, your BMI is too high, you should consult a doctor.";
    return array ("explain" => $spiegazione);
  }





  //Controlli blocanti sul sonno
  if ($sleep == 0) {
      $spiegazione = "You can't train you did not get any sleep!";
      return array ("explain" => $spiegazione);
  } else if ($sleep < 390) {
      $spiegazione = "I recommend you a lighter workout beacuse you did not get enough sleep";
      $scelta = rand(0,1);

      switch ($scelta) {
      	case '0':
      		$allenamento = fetchWorkout("Yoga",null);
      		break;
      	
      	default:
      		$allenamento = fetchWorkout("Wellness",null);
      		break;
      }

      if(isset($allenamento)){
      	foreach ($allenamento as $key => $value) {
      		$urlAllenamento = $key;
      		$imgUrlAllenamento = $value;
      		break;
      	}

      }


      return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);
  }




  //Passati i controlli bloccanti, inizia la raccomandazione effettiva basata su umore e bmi



       // casi sottopeso severo
          if ($bmi < 16.5 && $mood == 'fear') {
              //echo ("Il sistema ti consiglia Strenght o Streching<br>");
             $scelta = rand(0,1);
             $sceltaStringa;

             switch ($scelta) {
               case '0':
                 $allenamento = fetchWorkout("Strenght",null);
                 $sceltaStringa = "strenght";
                 break;
               
               default:
                 $allenamento = fetchWorkout("Stretching",null);
                 $sceltaStringa = "stretching";
                 break;
             }

             if(isset($allenamento)){
               foreach ($allenamento as $key => $value) {
                 $urlAllenamento = $key;
                 $imgUrlAllenamento = $value;
                 break;
               }

             }

             $spiegazione = "I recommend you this workout because you are severly underweight(based on your BMI and you are feeling fear.";
             if($sceltaStringa == "strenght"){
               $spiegazione = $spiegazione." A streght based workout will help you to grow muscle mass";
             }else if($sceltaStringa == "stretching"){
               $spiegazione = $spiegazione." Stretching will help you to relax your muscles, making you feel calm";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


          } else if ($bmi < 16.5 && $mood == 'anger') {
             // echo "il sistema ti consiglia Strengh o Wellness<br>";
             $scelta = rand(0,1);
             $sceltaStringa;

             switch ($scelta) {
               case '0':
                 $allenamento = fetchWorkout("Strenght",null);
                 $sceltaStringa = "strenght";
                 break;
               
               default:
                 $allenamento = fetchWorkout("Wellness",null);
                 $sceltaStringa = "wellness";
                 break;
             }

             if(isset($allenamento)){
               foreach ($allenamento as $key => $value) {
                 $urlAllenamento = $key;
                 $imgUrlAllenamento = $value;
                 break;
               }

             }

             $spiegazione = "I recommend you this workout because you are severly underweight(based on your BMI) and you are feeling angry.";
             if($sceltaStringa == "strenght"){
               $spiegazione = $spiegazione." A streght based workout will help you to grow muscle mass";
             }else if($sceltaStringa == "wellness"){
               $spiegazione = $spiegazione." A lighter workout will help you quench your anger";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



          } else if ($bmi < 16.5 && ($mood == 'sad' || $mood == 'disgust')) {
              //echo "Il sistema ti consiglia Yoga o Wellness<br>";
             $scelta = rand(0,1);
             $sceltaStringa;

             switch ($scelta) {
               case '0':
                 $allenamento = fetchWorkout("Yoga",null);
                 $sceltaStringa = "yoga";
                 break;
               
               default:
                 $allenamento = fetchWorkout("Wellness",null);
                 $sceltaStringa = "wellness";
                 break;
             }

             if(isset($allenamento)){
               foreach ($allenamento as $key => $value) {
                 $urlAllenamento = $key;
                 $imgUrlAllenamento = $value;
                 break;
               }

             }

             $spiegazione = "I recommend you this workout because you are severly underweight(based on your BMI) and you are feeling ".$mood.".";
             if($sceltaStringa == "yoga"){
               $spiegazione = $spiegazione." A yoga workout will help you mproving your mood";
             }else if($sceltaStringa == "wellness"){
               $spiegazione = $spiegazione." A lighter workout will help you improving your mood, rasing up your dopamine level.";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




          } else if ($bmi < 16.5 && ($mood == 'joy' || $mood == 'surprise' || $mood == 'neutrality')) {
              
                   if(aumentoLivello()){


                   } 

                   $spiegazione = "I recommend you this workout because you are severly underweight(based on your BMI) and you are feeling ".$mood.".";
                   $allenamento = fetchWorkout("Strenght",1);

                   if(isset($allenamento)){
                     //Avvaloro i link
                     foreach ($allenamento as $key => $value) {
                       $urlAllenamento = $key;
                       $imgUrlAllenamento = $value;
                       break;
                     }

                   }

               return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento, "explain" => $spiegazione);

          }









  // casi sottopeso

  if (($bmi >= 16.5 && $bmi <= 18.4) && $mood == 'fear') {

     // echo ("Il sistema ti consiglia  Stretching o Strenght<br>");
     $scelta = rand(0,1);
     $sceltaStringa;

     switch ($scelta) {
       case '0':
         $allenamento = fetchWorkout("Stretching",null);
         $sceltaStringa = "stretching";
         break;
       
       default:
         $allenamento = fetchWorkout("Strenght",null);
         $sceltaStringa = "strenght";
         break;
     }

     if(isset($allenamento)){
       foreach ($allenamento as $key => $value) {
         $urlAllenamento = $key;
         $imgUrlAllenamento = $value;
         break;
       }

     }

     $spiegazione = "I recommend you this workout because you are underweight(based on your BMI) and you are feeling fear.";
     if($sceltaStringa == "stretching"){
       $spiegazione = $spiegazione." Stretching will help you relaxing your muscle, calming you down";
     }else if($sceltaStringa == "strenght"){
       $spiegazione = $spiegazione." A stretght workout will help you growing muscle mass as long as you follow proper nutrition";
     }
     return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


  } else if (($bmi >= 16.5 && $bmi <= 18.4) && $mood == 'anger') {
      //echo "il sistema ti consiglia Combat , Strenght<br>";

     $scelta = rand(0,1);
     $sceltaStringa;

     switch ($scelta) {
       case '0':
         $allenamento = fetchWorkout("Combat",null);
         $sceltaStringa = "combat";
         break;
       
       default:
         $allenamento = fetchWorkout("Strenght",null);
         $sceltaStringa = "strenght";
         break;
     }

     if(isset($allenamento)){
       foreach ($allenamento as $key => $value) {
         $urlAllenamento = $key;
         $imgUrlAllenamento = $value;
         break;
       }

     }

     $spiegazione = "I recommend you this workout because you are underweight(based on your BMI) and you are feeling angry.";
     if($sceltaStringa == "strenght"){
       $spiegazione = $spiegazione." A strenght workout will help you growing muscle mass as long as you follow proper nutrition";
     }else if($sceltaStringa == "combat"){
       $spiegazione = $spiegazione." A combat workout will help you vent your anger";
     }
     return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



  } else if (($bmi >= 16.5 && $bmi <= 18.4) && ($mood == 'sad' || $mood == 'disgust')) {

      
       $scelta = rand(0,1);
       $sceltaStringa;

       switch ($scelta) {
         case '0':
           $allenamento = fetchWorkout("Yoga",null);
           $sceltaStringa = "yoga";
           break;
         
         default:
           $allenamento = fetchWorkout("Wellness",null);
           $sceltaStringa = "wellness";
           break;
       }

       if(isset($allenamento)){
         foreach ($allenamento as $key => $value) {
           $urlAllenamento = $key;
           $imgUrlAllenamento = $value;
           break;
         }

       }

       $spiegazione = "I recommend you this workout because you are underweight(based on your BMI) and you are feeling ".$mood.".";
       if($sceltaStringa == "yoga"){
         $spiegazione = $spiegazione." A yoga workout will help you finding yourself again, improving your mood";
       }else if($sceltaStringa == "wellness"){
         $spiegazione = $spiegazione." A lighter workout will help you improving your mood by rasing up your dopamin level.";
       }
       return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


  } else if (($bmi >= 16.5 && $bmi <= 18.4) && ($mood == 'joy' || $mood == 'surprise' || $mood == 'neutrality')) {

          if(aumentoLivello()){


          }

           $spiegazione = "I recommend you this workout because you are underweight(based on your BMI) and you are feeling ".$mood.". A strenght workout will help you growing muscle mass as long as you follow proper nutrition";
           $allenamento = fetchWorkout("Strenght",2);

           if(isset($allenamento)){
             //Avvaloro i link
             foreach ($allenamento as $key => $value) {
               $urlAllenamento = $key;
               $imgUrlAllenamento = $value;
               break;
             }

           }

       return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento, "explain" => $spiegazione); 


      
  }

















  // casi normopeso

  if (($bmi >= 18.5 && $bmi <= 24.9) && $mood == 'fear') {

    

     $scelta = rand(0,1);
     $sceltaStringa;

     switch ($scelta) {
       case '0':
         $allenamento = fetchWorkout("Cardio",null);
         $sceltaStringa = "cardio";
         break;
       
       default:
         $allenamento = fetchWorkout("Stretching",null);
         $sceltaStringa = "stretching";
         break;
     }

     if(isset($allenamento)){
       foreach ($allenamento as $key => $value) {
         $urlAllenamento = $key;
         $imgUrlAllenamento = $value;
         break;
       }

     }

     $spiegazione = "I recommend you this workout because you have normal weight(based on your BMI) and you are feeling fear.";
     if($sceltaStringa == "cardio"){
       $spiegazione = $spiegazione." A cardio workout will help you improving your mood, rasing up your dopamine level.";
     }else if($sceltaStringa == "stretching"){
       $spiegazione = $spiegazione." Stretching will help you relaxing your muscle, calming you down";
     }
     return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




  } else if (($bmi >= 18.5 && $bmi <= 24.9) && $mood == 'anger') {



  

           $spiegazione = "I recommend you this workout because you have normal weight(based on your BMI) and you are feeling agry. A combat workout will help you vent your anger and lose weight.";
           $allenamento = fetchWorkout("Combat",null);

           if(isset($allenamento)){
             //Avvaloro i link
             foreach ($allenamento as $key => $value) {
               $urlAllenamento = $key;
               $imgUrlAllenamento = $value;
               break;
             }

           }

       return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento, "explain" => $spiegazione);


  } else if (($bmi >= 18.5 && $bmi <= 24.9) && ($mood == 'sad' || $mood == 'disgust')) {

       $scelta = rand(0,1);
       $sceltaStringa;

       switch ($scelta) {
         case '0':
           $allenamento = fetchWorkout("Yoga",null);
           $sceltaStringa = "yoga";
           break;
       
         default:
           $allenamento = fetchWorkout("Cardio",null);
           $sceltaStringa = "cardio";
           break;
       }

       if(isset($allenamento)){
         foreach ($allenamento as $key => $value) {
           $urlAllenamento = $key;
           $imgUrlAllenamento = $value;
           break;
         }

       }

       $spiegazione = "I recommend you this workout because you have normal weight(based on your BMI) and you are feeling ".$mood.".";

       if($sceltaStringa == "cardio"){
         $spiegazione = $spiegazione." A cardio workout will help you improving your mood, rasing up your dopamine level.";
       }else if($sceltaStringa == "yoga"){
         $spiegazione = $spiegazione." A yoga workout will help you finding yourself again";
       }

       return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


  } else if (($bmi >= 18.5 && $bmi <= 29.9) && ($mood == 'joy' || $mood == 'surprise' || $mood == 'neutrality')) {

      if (aumentoLivello()) {
          

      } else {

         $scelta = rand(0,2);
         $sceltaStringa;

         switch ($scelta) {
           case '0':
             $allenamento = fetchWorkout("Strenght",null);
             $sceltaStringa = "strenght";
             break;

           case '1':
             $allenamento = fetchWorkout("Hiit",null);
             $sceltaStringa = "hiit";
             break;  
           
           default:
             $allenamento = fetchWorkout("Cardio",null);
             $sceltaStringa = "cardio";
             break;
         }

         if(isset($allenamento)){
           foreach ($allenamento as $key => $value) {
             $urlAllenamento = $key;
             $imgUrlAllenamento = $value;
             break;
           }

         }

         $spiegazione = "I reccomend you this workout because you have normal weight(based on your BMI) and you are feeling ".$mood.", then you perform any kind of workout";
  
         return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



      }

     
  }

























        // Casi sovrappeso

        if (($bmi >= 25 && $bmi <= 30) && $mood == 'fear') {
            //echo ("Il sistema ti consiglia  Cardio o Wellness o Stretching<br>");

            $scelta = rand(0,2);
            $sceltaStringa;

            switch ($scelta) {
              case '0':
                $allenamento = fetchWorkout("Stretching",null);
                $sceltaStringa = "stretching";
                break;

              case '1':
                $allenamento = fetchWorkout("Wellness",null);
                $sceltaStringa = "wellness";
                break;  
              
              default:
                $allenamento = fetchWorkout("Cardio",null);
                $sceltaStringa = "cardio";
                break;
            }

            if(isset($allenamento)){
              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

            $spiegazione = "I recommend you this workout because you are overweight(based on your BMI) and you are feeling fear.";

            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." A cardio workout will help you improving your mood, rasing up your dopamine level";
            }else if($sceltaStringa == "wellness"){
              $spiegazione = $spiegazione." A wellness workout will help you improving your mood.";
            }else if($sceltaStringa == "stretching"){
              $spiegazione = $spiegazione." Stretching will help you relaxing your muscle, calming you down";
            }

            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




        } else if (($bmi >= 25 && $bmi <= 30) && $mood == 'anger') {

                 $spiegazione = "I recommend you this workout because you are overweight(based on your BMI) and you are feeling agry. A combat workout will help you vent your anger and lose weight.";
                 $allenamento = fetchWorkout("Combat",null);

                 if(isset($allenamento)){
                   //Avvaloro i link
                   foreach ($allenamento as $key => $value) {
                     $urlAllenamento = $key;
                     $imgUrlAllenamento = $value;
                     break;
                   }

                 }

             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento, "explain" => $spiegazione);
        } else if (($bmi >= 25 && $bmi <= 30) && ($mood == 'sad' || $mood == 'disgust')) {

           

             $scelta = rand(0,1);
             $sceltaStringa;

             switch ($scelta) {
               case '0':
                 $allenamento = fetchWorkout("Yoga",null);
                 $sceltaStringa = "yoga";
                 break;
               
               default:
                 $allenamento = fetchWorkout("Cardio",null);
                 $sceltaStringa = "cardio";
                 break;
             }

             if(isset($allenamento)){
               foreach ($allenamento as $key => $value) {
                 $urlAllenamento = $key;
                 $imgUrlAllenamento = $value;
                 break;
               }

             }

             $spiegazione = "I recommend you this workout because you are overweight(based on your BMI) and you are feeling ".$mood.".";
             if($sceltaStringa == "cardio"){
               $spiegazione = $spiegazione." A cardio workout will help you improving your mood, rasing up your dopamine level.";
             }else if($sceltaStringa == "yoga"){
               $spiegazione = $spiegazione." A yoga workout will help you finding yourself again";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


        } else if (($bmi >= 25 && $bmi <= 30) && ($mood == 'joy' || $mood == 'surprise' || $mood == 'neutrality')) {

            if (aumentoLivello()) {
               

            } else {

               

               $spiegazione = "I recommend you this workout because you are overweight(based on your BMI) and you are feeling ".$mood.".";
               

               $scelta = rand(1, 2);

               switch ($scelta) {
                 case '1':
                   $allenamento = fetchWorkout("Cardio",null);
                   break;
                 
                 default:
                   $allenamento = fetchWorkout("Hiit",null);
                   break;
               }


               if(isset($allenamento)){

                 foreach ($allenamento as $key => $value) {

                   $urlAllenamento = $key;
                   $imgUrlAllenamento = $value;
                   break;
                 }

                return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);
         
            }


        }


      }

















         //Caso obesita di primo grado

         if (($bmi >= 30.1 && $bmi <= 34.9) && $mood == 'fear') {

            $scelta = rand(0,1);
            $sceltaStringa;

            switch ($scelta) {
              case '0':
                $allenamento = fetchWorkout("Cardio",null);
                $sceltaStringa = "cardio";
                break;
              
              default:
                $allenamento = fetchWorkout("Stretching",null);
                $sceltaStringa = "stretching";
                break;
            }

            if(isset($allenamento)){

              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

            $spiegazione = "I recommend you this workout because you are in class first obesity(based on your BMI) and you are feeling fear.";
            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." A cardio workout will help you reducing your heart rate, relaxing yourself";
            }else if($sceltaStringa == "stretching"){
              $spiegazione = $spiegazione." Stretching will help you relaxing your muscle, calming you down";
            }
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);

         } else if (($bmi >= 30.1 && $bmi <= 34.9) && $mood == 'anger') {


            


            $scelta = rand(0,1);
            $sceltaStringa;

            switch ($scelta) {
              case '0':
                $allenamento = fetchWorkout("Combat",null);
                $sceltaStringa = "combat";
                break;
              
              default:
                $allenamento = fetchWorkout("Cardio",null);
                $sceltaStringa = "cardio";
                break;
            }

            if(isset($allenamento)){
              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

            $spiegazione = "I recommend you this workout because you are in class first obesity(based on your BMI) and you are feeling angry.";
            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." A cardio workout will help you reducing your heart rate, relaxing yourself";
            }else if($sceltaStringa == "combat"){
              $spiegazione = $spiegazione." A combat workout will help you vent your anger";
            }
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



         } else if (($bmi >= 30.1 && $bmi <= 34.9) && ($mood == 'sad' || $mood == 'disgust')) {
             //echo "Il sistema ti consiglia Yoga o Wellness o Cardio<br>";

            $scelta = rand(0,2);
            $sceltaStringa;

            switch ($scelta) {
              case '0':
                $allenamento = fetchWorkout("Yoga",null);
                $sceltaStringa = "yoga";
                break;

              case '1':
                $allenamento = fetchWorkout("Wellness",null);
                $sceltaStringa = "wellness";
                break;  
              
              default:
                $allenamento = fetchWorkout("Cardio",null);
                $sceltaStringa = "cardio";
                break;
            }

            if(isset($allenamento)){
              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

             $spiegazione = "I recommend you this workout because you are in class first obesity(based on your BMI) and you are feeling".$mood.".";

            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." A cardio workout will help you improving your mood, rasing up your dopamine level.";
            }else if($sceltaStringa == "wellness"){
              $spiegazione = $spiegazione." A lighter workout will help you improve your mood";
            }else if($sceltaStringa == "yoga"){
              $spiegazione = $spiegazione." A yoga workout will help you finding yourself again";
            }

            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




         } else if (($bmi >= 30.1 && $bmi <= 34.9) && ($mood == 'joy' || $mood == 'surprise' || $mood == 'neutrality')) {


             if (aumentoLivello()) {
                 

             } else {
                 // Il sistema sceglie casualmente uno dei tanti allenamenti e avvalora
                 // ultimo allenamento è quello che succederà nella versione finale del recommender
                 //echo "Il sistema ti consigli Cardio o HIIT ";
                  //echo"ci siamo";
                 $spiegazione = "I recommend you this workout because you are in class first obesity(based on your BMI) and you are feeling ".$mood.".";
                 $propensioneLivelloSu = true;

                 $scelta = rand(1, 2);

                 switch ($scelta) {
                 	case '1':
                 		$allenamento = fetchWorkout("Cardio",$scelta);
                 		break;
                 	
                 	default:
                 		$allenamento = fetchWorkout("Hiit",$scelta);
                 		break;
                 }


                 if(isset($allenamento)){

                 	foreach ($allenamento as $key => $value) {
                 		$urlAllenamento = $key;
                 		$imgUrlAllenamento = $value;
                 		break;
                 	}

                  
                 }


               

             }

              return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione); 
         }


          //Caso obesità di secondo grado


         if (($bmi >= 35 && $bmi < 40) && $mood == 'fear') {

            $scelta = rand(0,1);
            $sceltaStringa;

            switch ($scelta) {
              case '0':
                $allenamento = fetchWorkout("Cardio",null);
                $sceltaStringa = "cardio";
                break;
              
              default:
                $allenamento = fetchWorkout("Stretching",null);
                $sceltaStringa = "stretching";
                break;
            }

            if(isset($allenamento)){

              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

            $spiegazione = "I recommend you this workout because you are in class second obesity(based on your BMI) and you are feeling fear.";
            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." A cardio workout will help you reducing your heart rate, relaxing yourself";
            }else if($sceltaStringa == "stretching"){
              $spiegazione = $spiegazione." Stretching will help you relaxing your muscle, calming you down";
            }
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);

         } else if (($bmi >= 35 && $bmi < 40) && $mood == 'anger') {


            $allenamento = fetchWorkout("Combat",null);


            if(isset($allenamento)){
              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

            $spiegazione = "I recommend you this workout because you are in class second obesity(based on your BMI) and you are feeling angry.A cardio workout will help you reducing your heart rate, relaxing yourself";
  
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



         } else if (($bmi >= 35 && $bmi < 40) && ($mood == 'sad' || $mood == 'disgust')) {
             //echo "Il sistema ti consiglia Yoga o Wellness o Cardio<br>";

            $scelta = rand(0,2);
            $sceltaStringa;

            switch ($scelta) {
              case '0':
                $allenamento = fetchWorkout("Yoga",null);
                $sceltaStringa = "yoga";
                break;

              case '1':
                $allenamento = fetchWorkout("Wellness",null);
                $sceltaStringa = "wellness";
                break;  
              
              default:
                $allenamento = fetchWorkout("Cardio",null);
                $sceltaStringa = "cardio";
                break;
            }

            if(isset($allenamento)){
              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

             $spiegazione = "I recommend you this workout because you are in class second obesity(based on your BMI) and you are feeling".$mood.".";

            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." A cardio workout will help you improving your mood, rasing up your dopamine level.";
            }else if($sceltaStringa == "wellness"){
              $spiegazione = $spiegazione." A lighter workout will help you improve your mood";
            }else if($sceltaStringa == "yoga"){
              $spiegazione = $spiegazione." A yoga workout will help you finding yourself again";
            }

            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




         } else if (($bmi >= 35 && $bmi < 40) && ($mood == 'joy' || $mood == 'surprise' || $mood == 'neutrality')) {


             if (aumentoLivello()) {
                 

             } else {
                 // Il sistema sceglie casualmente uno dei tanti allenamenti e avvalora
                 // ultimo allenamento è quello che succederà nella versione finale del recommender
                 //echo "Il sistema ti consigli Cardio o HIIT ";
                  //echo"ci siamo";
                 $spiegazione = "I recommend you this workout because you are in class second obesity(based on your BMI) and you are feeling".$mood.".";
                 $propensioneLivelloSu = true;

                 $scelta = rand(1, 2);

                 switch ($scelta) {
                  case '1':
                    $allenamento = fetchWorkout("Cardio",null);
                    break;
                  
                  default:
                    $allenamento = fetchWorkout("Hiit",null);
                    break;
                 }


                 if(isset($allenamento)){

                  foreach ($allenamento as $key => $value) {
                    $urlAllenamento = $key;
                    $imgUrlAllenamento = $value;
                    break;
                  }

                  
                 }


               

             }

              return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione); 
         }















         //siamo fuori da tutto qui
         return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


} //Fine recommendWorkout












function retriveWorkout($resp, $parameters, $text, $email)
{



	$imgUrlAllenamento = null;
	$urlAllenamento;

	$allenamento = null;
	//print_r($parameters);

	$difficolta = null; //di default è nulla


	//print($text);

	if(strpos($text, 'light') !== false ){

		$difficolta = 1;

	}else if( strpos($text, 'normal') !== false  ){

		$difficolta = 3;

	}else if( strpos($text, 'intense') !== false){

		$difficolta = 4;
	}




	foreach ($parameters as $key => $value) {

		switch ($key) {
			case 'AllenamentoUpperBody':

          if(isset($value) && $value != "" ){

            if(isset($difficolta)){
              $allenamento = fetchWorkout("UpperBody", $difficolta);
              break;
            }else{
              $allenamento = fetchWorkout("UpperBody",null);
              break;
            } 

          }
          


			case 'AllenamentoStretching':	

      if(isset($value) && $value != ""){


        if(isset($difficolta)){
          $allenamento = fetchWorkout("Stretching", $difficolta);
          break;
        }else{
          $allenamento = fetchWorkout("Stretching",null);
          break;
        }
        


      }
      


			case 'AllenamentoCardio':

        if(isset($value) && $value != ""){


          if(isset($difficolta)){
            $allenamento = fetchWorkout("Cardio", $difficolta);
            break;
          }else{
            $allenamento = fetchWorkout("Cardio",null);
            break;
          }
          
          

        }
        

			case 'AllenamentoYoga':

      if(isset($value)&& $value != ""){

        if(isset($difficolta)){

          if($difficolta == 4){
            $difficolta = 3; //Non ci sono allenamenti Yoga di difficoltà 4, quindi usiamo la 3.
          }
          $allenamento = fetchWorkout("Yoga", $difficolta);
          break;
        }else{
          $allenamento = fetchWorkout("Yoga",null);
          break;
        }
        

      }
      


			case 'AllenamentoAbs':

        if(isset($value)&& $value != ""){

          if(isset($difficolta)){
            $allenamento = fetchWorkout("Abs", $difficolta);
            break;

          }else{
            $allenamento = fetchWorkout("Abs",null);
            break;

          }
          

        }

        

			case 'AllenamentoForza':

        if(isset($value)&& $value != ""){

          if(isset($difficolta)){
            $allenamento = fetchWorkout("Strenght", $difficolta);
            break; 
          }else{
            $allenamento = fetchWorkout("Strenght",null);
            break; 
          }
           

        }
		    
				
			case 'AllenamentoLowerBody':
        if(isset($value)&& $value != ""){

          if(isset($difficolta)){
            $allenamento = fetchWorkout("LowerBody", $difficolta);
            break;
          }else{
            $allenamento = fetchWorkout("LowerBody",null);
            break;
          }
          


        }
        
      case 'AllenamentoHiit':
        if(isset($value) && $value != ""){

          if(isset($difficolta)){
            $allenamento = fetchWorkout("Hiit", $difficolta);
            break;
          }else{
            $allenamento = fetchWorkout("Hiit", null);
            break;
          }

        }

			case 'AllenamentoWellness':

          if(isset($value)&& $value != ""){

            $allenamento = fetchWorkout("Wellness",1);
            break;    
          }


      case 'AllenamentoCombat':
          if(isset($value) && $value != ""){

            if(isset($difficolta)){
              $allenamento = fetchWorkout("Combat", $difficolta);
              break;
            }else{
              $allenamento = fetchWorkout("Combat", null);
              break;
            }

          }      
          

			default:
				//echo "Parametro non voluto avvalorato";
				break;
		}//fine switch



	}


	if(isset($allenamento)){

		//Avvaloro i link
		foreach ($allenamento as $key => $value) {
			$urlAllenamento = $key;
			$imgUrlAllenamento = $value;
			break;
		}


	}

   return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento );
	
}





function aumentoLivello()
{
  #TODO: Gestire l'aumento di livello

return false;
}






?>







