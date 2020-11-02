<?php



function fetchWorkout($nomeParzialeFile, $difficolta)
{
    $url = null;
    $imgUrl = null;
    $result = null;

 
    if (isset($difficolta)) {

        //echo "<br>DIFFICOLTA' SETTATA<br>";

        $difficoltaLocale = $difficolta;

        //$nomeFile = $nomeParzialeFile . $difficoltaLocale . ".txt";
        $nomeFile = $nomeParzialeFile . $difficoltaLocale; 
        $nomeFile = $nomeParzialeFile."/".$nomeFile. ".txt";



       // echo "Il nome del file e: ". $nomeFile;
       // echo "Il nome della costante DIR e: ".__DIR__;

        $percorso = __DIR__."/WorkoutRecSys";
        $nomeFile = $percorso."/".$nomeFile;

        //print($nomeFile);

        


        
        $nRighe = getRigheNonVuoteFile($nomeFile);
        
        
        $rigaCasuale = rand(0, $nRighe);
        
        // echo "<br> La Riga scelta a caso: " . $rigaCasuale . "<br>";
        
        $riga = file($nomeFile); // restituisce array con tutte le righe
       

        

        
        $rigavera = implode(",", $riga); // lo rendo stringa
        $presenza = explode(",", $rigavera);
        
        

        
        if($rigaCasuale != 0){
            
            $indicePrimoElemento = (2 * ($rigaCasuale - 1) +1) -1; //tutto -1 perchè si parte da 0
            $indiceSecondoElemento = $indicePrimoElemento + 1;
            

            
            
            $url = trim($presenza[$indicePrimoElemento]);
         
            $imgUrl = trim($presenza[$indiceSecondoElemento]);
         
            
            
        }else{
            
            $indicePrimoElemento = 0;
            $indiceSecondoElemento = $indicePrimoElemento + 1;
            
            
            $url = trim($presenza[$indicePrimoElemento]);
           
            $imgUrl = trim($presenza[$indiceSecondoElemento]);
           
            
            
            
            
        }//fine else
        
        
        
        $result = [
            $url => $imgUrl
        ];


    }else {
        
        //echo"DIFFICOLTA NON SETTATA<br><br>";
        
        $nomeFile = $nomeParzialeFile . ".txt";
        $nomeFile = $nomeParzialeFile."/".$nomeFile;

        $percorso = __DIR__."/WorkoutRecSys";
        $nomeFile = $percorso."/".$nomeFile;

        //print($nomeFile);

        
        $nRighe = getRigheNonVuoteFile($nomeFile);
        
        $rigaCasuale = rand(0, $nRighe);
        
       
        
        $riga = file($nomeFile); // restituisce array con tutte le righe

        
        

                
             
                
                
                $rigavera = implode(",", $riga); // lo rendo stringa
                $presenza = explode(",", $rigavera);
               

                
                if($rigaCasuale != 0){
                    
                    $indicePrimoElemento = (2 * ($rigaCasuale - 1) +1) -1; //tutto -1 perchè si parte da 0
                    $indiceSecondoElemento = $indicePrimoElemento + 1;


                    
                    
                    $url = trim($presenza[$indicePrimoElemento]);

                    $imgUrl = trim($presenza[$indiceSecondoElemento]);

                 
                    
                }else{
                    
                    $indicePrimoElemento = 0;
                    $indiceSecondoElemento = $indicePrimoElemento + 1;
                    
                    
                    $url = trim($presenza[$indicePrimoElemento]);

                    $imgUrl = trim($presenza[$indiceSecondoElemento]);
                    
                   
                   
                    
                    
                }//fine else
                
                

        
        $result = [
            $url => $imgUrl
        ];
        
       

    } // fine else
    


    return $result;
}// fine FetchWorkout



/*
 * Funzione che prende un file in input e restituisce un intero che indica il numero delle sue righe
 * non vuote.
 *
 */
function getRigheNonVuoteFile($file)
{
    $numeroRighe = 0;
   
    foreach (file($file) as $riga) {

        if ($riga != "") {
            $numeroRighe ++;
        }
    }
    
    return $numeroRighe;
}



?>