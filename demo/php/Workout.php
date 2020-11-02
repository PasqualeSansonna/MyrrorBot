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

   // $mood = 'sorpresa'; //None sarebbe la neutralità. mood var usata per i test

    $mood = 'sorpresa';
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
  //$bmi = 39.9;

//print($bmi);
//$bmi = 24.9;

  //$nome = getName($email);

//  echo"Il nome e': ".$nome;



  //inizio raccomandazione effettiva

  // Controlli bloccanti sul cuore
  if ($restingHeartRate < 60) {
      $spiegazione = "Non ti puoi allenare, controlla il tuo cuore";
      return array ("explain" => $spiegazione);
  }else if($restingHeartRate > 90){
      $spiegazione = "Ti consiglio questo allenamento perchè il tuo battito cardiaco è alto e vorrei che tu abbassassi la tua frequenza cardiaca";
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
    $spiegazione = "Non puoi utilizzare questo serizio, il tuo BMI è troppo alto, consulta il tuo medico.";
    return array ("explain" => $spiegazione);
  }






  //Controlli blocanti sul sonno
  if ($sleep == 0) {
      $spiegazione = "Non puoi allenarti, non hai dormito";
      return array ("explain" => $spiegazione);
  } else if ($sleep < 390) {
      $spiegazione = "Ti consiglio questo allenamento leggero, poichè hai dormito poco";
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
          if ($bmi < 16.5 && $mood == 'paura') {
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

             $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sottopeso grave(calcolato in base al tuo bmi) e stai provando paura.";
             if($sceltaStringa == "strenght"){
               $spiegazione = $spiegazione." Un allenamento di forza ti aiuterà a costruire massa muscolare";
             }else if($sceltaStringa == "stretching"){
               $spiegazione = $spiegazione." Lo stretching ti aiuterà a rilassare la muscolatura, calmandoti";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


          } else if ($bmi < 16.5 && $mood == 'rabbia') {
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

             $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sottopeso grave(calcolato in base al tuo bmi) e stai provando rabbia.";
             if($sceltaStringa == "strenght"){
               $spiegazione = $spiegazione." Un allenamento di forza ti aiuterà a costruire massa muscolare";
             }else if($sceltaStringa == "wellness"){
               $spiegazione = $spiegazione." Un allenamento leggero ti aiuterà a far sbollire la rabbia";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



          } else if ($bmi < 16.5 && ($mood == 'tristezza' || $mood == 'disgusto')) {
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

             $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sottopeso grave(calcolato in base al tuo bmi) e stai provando ".$mood.".";
             if($sceltaStringa == "yoga"){
               $spiegazione = $spiegazione." Un allenamento di yoga ti aiuterà a migliorare il tuo umore";
             }else if($sceltaStringa == "wellness"){
               $spiegazione = $spiegazione." Un allenamento leggero ti aiuterà a migliorare il tuo umore, aumentando la tua produzione di dopamina";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




          } else if ($bmi < 16.5 && ($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità')) {
              
                   if(aumentoLivello()){


                   } 

                   $spiegazione = "Ti consiglio questo allenamento perchè sei in uno stato di sottopeso grave(calcolato in base al tuo bmi) e stai provando ".$mood.".";
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

  if (($bmi >= 16.5 && $bmi <= 18.4) && $mood == 'paura') {

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

     $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sottopeso(calcolato in base al tuo bmi) e stai provando rabbia.";
     if($sceltaStringa == "stretching"){
       $spiegazione = $spiegazione." Una sessione di stretching ti aiuterà a rilassare i muscoli, tranquillizzandoti";
     }else if($sceltaStringa == "strenght"){
       $spiegazione = $spiegazione." Un allenamento di forza ti aiuterà a costruire massa muscolare, a patto che tu segua una buona alimentazione";
     }
     return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


  } else if (($bmi >= 16.5 && $bmi <= 18.4) && $mood == 'rabbia') {
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

     $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sottopeso(calcolato in base al tuo bmi) e stai provando rabbia.";
     if($sceltaStringa == "strenght"){
       $spiegazione = $spiegazione." Un allenamento di forza ti aiuterà a costruire massa muscolare, a patto che tu segua una buona alimentazione";
     }else if($sceltaStringa == "combat"){
       $spiegazione = $spiegazione." Un allenamento di arti marziali ti aiuterà a sfogare la tua rabbia";
     }
     return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



  } else if (($bmi >= 16.5 && $bmi <= 18.4) && ($mood == 'tristezza' || $mood == 'disgusto')) {

      
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

       $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sottopeso (calcolato in base al tuo bmi) e stai provando ".$mood.".";
       if($sceltaStringa == "yoga"){
         $spiegazione = $spiegazione." Un allenamento di yoga ti aiuterà a migliorare il tuo umore";
       }else if($sceltaStringa == "wellness"){
         $spiegazione = $spiegazione." Un allenamento leggero ti aiuterà a migliorare il tuo umore, aumentando la tua produzione di dopamina";
       }
       return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


  } else if (($bmi >= 16.5 && $bmi <= 18.4) && ($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità')) {

          if(aumentoLivello()){


          }

           $spiegazione = "Ti consiglio questo allenamento perchè sei in uno stato di sottopeso (calcolato in base al tuo bmi) e stai provando ".$mood.". Un alenamento di forza ti aiuterà ad aumentare la tua massa muscolare";
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

  if (($bmi >= 18.5 && $bmi <= 24.9) && $mood == 'paura') {

    

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

     $spiegazione = "Ti ho consigliato questo allenamento perchè sei normopeso(calcolato in base al tuo bmi) e stai provando paura.";
     if($sceltaStringa == "cardio"){
       $spiegazione = $spiegazione." Il cardio ti aiuterà ad abbassare la tua frequenza cardiaca, calmandoti";
     }else if($sceltaStringa == "stretching"){
       $spiegazione = $spiegazione." Lo stretching ti aiuterà a rilassare la muscolatura, calmandoti";
     }
     return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




  } else if (($bmi >= 18.5 && $bmi <= 24.9) && $mood == 'rabbia') {



  

           $spiegazione = "Ti consiglio questo allenamento perchè sei normopeso(calcolato in base al tuo bmi) e stai provando rabbia. Un allenamento di arti marziali ti aiuterà a sfogare la rabbia.";
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


  } else if (($bmi >= 18.5 && $bmi <= 24.9) && ($mood == 'tristezza' || $mood == 'disgusto')) {

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

       $spiegazione = "Ti ho consigliato questo allenamento perchè sei normopeso(calcolato in base al tuo bmi) e stai provando ".$mood.".";

       if($sceltaStringa == "cardio"){
         $spiegazione = $spiegazione." Il cardio ti aiuterà ad aumentare la tua produzione di dopamina, migliorando il tuo umore";
       }else if($sceltaStringa == "yoga"){
         $spiegazione = $spiegazione." Un allenamento yoga ti aiuterà a ritrovare te stesso";
       }

       return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


  } else if (($bmi >= 18.5 && $bmi <= 24.9) && ($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità')) {

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

         $spiegazione = "Ti ho consigliato questo allenamento perchè sei normopeso(calcolato in base al tuo bmi) e stai provando ".$mood.", quindi puoi svolgere qualunque tipo di allenamento";
  
         return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



      }

     
  }

























        // Casi sovrappeso

        if (($bmi >= 25 && $bmi <= 30) && $mood == 'paura') {
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

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei sovrappeso(calcolata in base al tuo bmi) e stai provando paura.";

            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." Il cardio ti aiuterà ad aumentare la tua produzione di dopamina, migliorando il tuo umore";
            }else if($sceltaStringa == "wellness"){
              $spiegazione = $spiegazione." Un allenamento per stare bene migliorerà il tuo umore";
            }else if($sceltaStringa == "stretching"){
              $spiegazione = $spiegazione." Un allenamento stretching ti aiuterà a rilassare la muscolatura, calmandoti";
            }

            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



        } else if (($bmi >= 25 && $bmi <= 30) && $mood == 'rabbia') {

                 $spiegazione = "Ti consiglio questo allenamento perchè sei sovrappeso(calcolato in base al tuo bmi) e stai provando rabbia. Un allenamento di arti marziali ti aiuterà a sfogare la rabbia e a perdere peso.";
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
        } else if (($bmi >= 25 && $bmi <= 30) && ($mood == 'tristezza' || $mood == 'disgusto')) {

           

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

             $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di sovrappeso(calcolato in base al tuo bmi) e stai provando ".$mood.".";
             if($sceltaStringa == "cardio"){
               $spiegazione = $spiegazione." Il cardio aumentarà la produzione di dopamina, migliorando il tuo umore";
             }else if($sceltaStringa == "yoga"){
               $spiegazione = $spiegazione." Un allenamento di yoga ti aiuterà a ritrovare te stesso";
             }
             return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);


        } else if (($bmi >= 25 && $bmi <= 30) && ($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità')) {

            if (aumentoLivello()) {
               

            } else {

               

               $spiegazione = "Ti consiglio questo allenamento perchè sei in uno stato di sovrappeso(determinato in base al tuo BMI) e sei in uno stato emotivo di: ".$mood.".";
               

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

         if (($bmi >= 30.1 && $bmi <= 34.9) && $mood == 'paura') {

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

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di obesità di primo grado(calcolato in base al tuo bmi) e stai provando paura.";
            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." Il cardio ti aiuterà ad abbassare la tua frequenza cardiaca, calmandoti";
            }else if($sceltaStringa == "stretching"){
              $spiegazione = $spiegazione." Lo stretching ti aiuterà a rilassare la muscolatura, calmandoti";
            }
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);

         } else if (($bmi >= 30.1 && $bmi <= 34.9) && $mood == 'rabbia') {


            


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

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di obesità di primo grado(calcolata in base al tuo bmi) e stai provando rabbia.";
            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." Il cardio ti aiuterà ad abbassare la tua frequenza cardiaca, calmandoti";
            }else if($sceltaStringa == "combat"){
              $spiegazione = $spiegazione." Un allenamento di arti marziali ti aiuterà a sfogare la tua rabbia";
            }
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



         } else if (($bmi >= 30.1 && $bmi <= 34.9) && ($mood == 'tristezza' || $mood == 'disgusto')) {
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

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di obesità di primo grado(calcolata in base al tuo bmi) e stai provando ".$mood.".";

            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." Il cardio ti aiuterà ad aumentare la tua produzione di dopamina, migliorando il tuo umore";
            }else if($sceltaStringa == "wellness"){
              $spiegazione = $spiegazione." Un allenamento per stare bene migliorerà il tuo umore";
            }else if($sceltaStringa == "yoga"){
              $spiegazione = $spiegazione." Un allenamento yoga ti aiuterà a ritrovare te stesso";
            }

            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




         } else if (($bmi >= 30.1 && $bmi <= 34.9) && ($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità')) {


             if (aumentoLivello()) {
                 

             } else {
                 // Il sistema sceglie casualmente uno dei tanti allenamenti e avvalora
                 // ultimo allenamento è quello che succederà nella versione finale del recommender
                 //echo "Il sistema ti consigli Cardio o HIIT ";
                  //echo"ci siamo";
                 $spiegazione = "Ti consiglio questo allenamento perchè sei in uno stato di obesità di primo grado(determinato in base al tuo BMI) e sei in uno stato emotivo di  ".$mood.".";
                 $propensioneLivelloSu = true;

                 $scelta = rand(1, 2);
                 //print($scelta);
                 switch ($scelta) {
                 	case '1':
                 		$allenamento = fetchWorkout("Cardio",$scelta);
                 		break;
                 	
                 	default:
                 		$allenamento = fetchWorkout("Hiit",3);
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


         if (($bmi >= 35 && $bmi < 40) && $mood == 'paura') {

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

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di obesità di secondo grado(calcolato in base al tuo bmi) e stai provando paura.";
            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." Il cardio ti aiuterà ad abbassare la tua frequenza cardiaca, calmandoti";
            }else if($sceltaStringa == "stretching"){
              $spiegazione = $spiegazione." Lo stretching ti aiuterà a rilassare la muscolatura, calmandoti";
            }
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);

         } else if (($bmi >= 35 && $bmi < 40) && $mood == 'rabbia') {


            $allenamento = fetchWorkout("Cardio",null);


            if(isset($allenamento)){
              foreach ($allenamento as $key => $value) {
                $urlAllenamento = $key;
                $imgUrlAllenamento = $value;
                break;
              }

            }

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di obesità di secondo grado(calcolata in base al tuo bmi) e stai provando rabbia. Un allenamento cardio ti aiuterà a far diminuire la tensione";
        
            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);



         } else if (($bmi >= 35 && $bmi < 40) && ($mood == 'tristezza' || $mood == 'disgusto')) {
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

            $spiegazione = "Ti ho consigliato questo allenamento perchè sei in uno stato di obesità di secondo grado(calcolata in base al tuo bmi) e stai provando ".$mood.".";

            if($sceltaStringa == "cardio"){
              $spiegazione = $spiegazione." Il cardio ti aiuterà ad aumentare la tua produzione di dopamina, migliorando il tuo umore";
            }else if($sceltaStringa == "wellness"){
              $spiegazione = $spiegazione." Un allenamento per stare bene migliorerà il tuo umore";
            }else if($sceltaStringa == "yoga"){
              $spiegazione = $spiegazione." Un allenamento yoga ti aiuterà a ritrovare te stesso";
            }

            return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento , "explain" => $spiegazione);




         } else if (($bmi >= 35 && $bmi < 40) && ($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità')) {


             if (aumentoLivello()) {
                 

             } else {
                 // Il sistema sceglie casualmente uno dei tanti allenamenti e avvalora
                 // ultimo allenamento è quello che succederà nella versione finale del recommender
                 //echo "Il sistema ti consigli Cardio o HIIT ";
                  //echo"ci siamo";
                 $spiegazione = "Ti consiglio questo allenamento perchè sei in uno stato di obesità di secondo grado(determinato in base al tuo BMI) e sei in uno stato emotivo di: ".$mood.".";
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

	if(strpos($text, 'facil') !== false || strpos($text, 'leggero') !== false ){

		$difficolta = 1;

	}else if( strpos($text, 'normal') !== false  ){

		$difficolta = 3;

	}else if( strpos($text, 'difficil') !== false || strpos($text, 'pesante')!== false){

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



//funzione nata per la sperimentazione, estende la raccomandazione anche al guest, suggerisce randomicamente
// un allenamento wellness, oppure uno cardio, oppure uno di forza, tutti di difficoltà normale.
function recommendWorkoutGuest($resp, $parameters, $text, $email)
{


  $scelta = rand(0,2);
  $allenamento=null;

  switch ($scelta) {
    case '0':
      $allenamento = fetchWorkout("Wellness",null);
      break;

    case '1':
      $allenamento = fetchWorkout("Strenght",3);
      break;  
    
    default:
      $allenamento = fetchWorkout("Cardio",3);
      break;
  }

  if(isset($allenamento)){
    foreach ($allenamento as $key => $value) {
      $urlAllenamento = $key;
      $imgUrlAllenamento = $value;
      break;
    }

  }

  return array( "url" => $urlAllenamento , "imgUrl" => $imgUrlAllenamento );


}







?>







