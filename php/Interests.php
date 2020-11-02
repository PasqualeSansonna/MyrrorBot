<?php

//Restuisce l'elenco dei maggiori interessi dell'utente. N.B. Non vengono presi in considerazione quelli che hanno la dicitura "Category:"
function interessi($resp,$parameters,$email){

	$param = "";
	$json_data = queryMyrror($param,$email);

	$categorieArray = array();

	foreach ($json_data as $key1 => $value1) {

		if($key1 == "interests"){
			foreach ($value1 as $key => $value) {
				if (isset($value['value'])) {//Verifico se è valorizzata la variabile 'value'

					$categoria = $value['value']; //Prendo la categoria

					if (strlen($categoria) <= 30) {//Se la frase è lunga meno di 30 caratteri

						//Includo solo le frasi che non contengono la dicitura "category","articles", "categoria"
						if (strpos(strtolower($categoria), 'category:') !== false || strpos(strtolower($categoria), 'categoria') !== false || strpos(strtolower($categoria), 'articles') !== false) {	
							//Non effettuo alcuna operazione
						}else{
							$categorieArray[] = $categoria; //Inserisco la categoria nell'array

							//Se l'array delle categorie non è vuoto, trovo gli interessi più frequenti
							if (count($categorieArray) != 0) {
								$top5 = interessiFrequenti($categorieArray);
							}						
						}
					}
				}
			}
        }	
    }

    //Se è valorizzato l'array, stampo gli interessi
	if (isset($top5)) {
		$answer = $resp;

		if (count($top5) != 0) {
			foreach ($top5 as $key => $value){
   				$answer = $answer . " " . $key .", " ;
        	}

        	//Rimuovo lo spazio con la virgola finale
        	$answer = substr($answer, 0, -2);
		}else {
			$answer = "Purtroppo non sono riuscito a recuperare i tuoi interessi &#x1F613; Riprova più tardi oppure controlla se nel tuo profilo sono presenti gli interessi!";
		}

	}else{
		$answer = "Purtroppo non sono riuscito a recuperare i tuoi interessi &#x1F613; Riprova più tardi oppure controlla se nel tuo profilo sono presenti gli interessi!";
	}

	//A volte la richiesta non restituisce nessun elenco perciò dovrà essere rifatta
	if ($answer == null) {
		$answer = "Non sono riuscito a caricare i tuoi interessi &#x1F613; Riprova più tardi";
	}

	return $answer;

}


//Calcolo i contatti con i quali l'utente ha interagito di più
function interessiFrequenti($categorieArray){
	
	$interessiFrequenti = array();

	$interessiFrequenti = array_count_values($categorieArray); //Genera un array associativo: nome->(occorrenze di nome)
	arsort($interessiFrequenti);//Ordino l'array in relazione al maggior numero di interazioni

	$top5 = array_slice($interessiFrequenti, 0, 5);//Prendo i primi cinque

	return $top5;
}

function getInterestsList($email){

    $top30 = array();
	$param = "";
	$json_data = queryMyrror($param,$email);

	$categorieArray = array();

	foreach ($json_data as $key1 => $value1) {

		if($key1 == "interests"){
			foreach ($value1 as $key => $value) {
				if (isset($value['value'])) {//Verifico se è valorizzata la variabile 'value'

					$categoria = $value['value']; //Prendo la categoria

					if (strlen($categoria) <= 30) {//Se la frase è lunga meno di 30 caratteri

						//Includo solo le frasi che non contengono la dicitura "category","articles", "categoria"
						if (strpos(strtolower($categoria), 'category:') !== false || strpos(strtolower($categoria), 'categoria') !== false || strpos(strtolower($categoria), 'articles') !== false) {	
							//Non effettuo alcuna operazione
						}else{
							$categorieArray[] = $categoria; //Inserisco la categoria nell'array

							//Se l'array delle categorie non è vuoto, trovo gli interessi più frequenti
							if (count($categorieArray) != 0) {
								$interessiFrequenti = array();

			                    $interessiFrequenti = array_count_values($categorieArray); //Genera un array associativo: nome->(occorrenze di nome)
			                    arsort($interessiFrequenti);//Ordino l'array in relazione al maggior numero di interazioni
		                          //prendo i primi 30 elementi
			                    $top30 = array_slice($interessiFrequenti, 0, 30);
								
							}					
						}
					}
					
				}
			}
        }	
    }
	return $top30;
}