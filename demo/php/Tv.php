<?php

//Funzione di retrive per programmi tv

//include 'script.py';



function retriveTV($resp,$parameters,$text,$email)
{


	$tipologiaRichiesta = null;
	$genereRichiesto = null;
	$canaleRichiesto = null;
	$personaggioRichiesto = null; //Da controllare con strpos, poichè non penso si possa gestire mediante dialogflow.
	$contenuto = array();
	$risposta ="";
	$finali =null;
	//echo('Siamo in retrive');

	//Gestione di avvio dello script

	//Diamo per assodato che il file sia già generato e lo apriamo

	$nomefile = date('d-m-Y');
	$nomefile = $nomefile.".csv";

	$path = '/var/www/html/demo/php/TvScripts/';
	$completeFile = $path.$nomefile;
	
	if(file_exists($completeFile)){
		//Usiamo quello esistente
		//echo"esiste";
		$file = $completeFile;

	}else{
		//eliminiamo i file vecchi nella cartella TvScript e lanciamo lo script per generare il nuovo file

		//eliminazione dei file

		$percorso = $path;
		if ($handle = opendir($percorso))
		{
		   while (false !== ($file = readdir($handle)))
		   {
		       //echo "$file\n";
		       $res = explode('.', $file);
		       if($res[1] == 'csv' || $res[1] == 'CSV'){
		       	//la cosa importante è questo, che rende il file da cancellare,cancellabile.
		       	//tuttavia il file creato deve avere già tutti i permessi,altrimenti, verrà si cancellato lo stesso
		       	//ma genererà un warning che "buggera" la prima domanda chiesta al bot
		       	chmod($percorso.$file, 0777);
		       	unlink($percorso.$file);
		       }
		      
		   }
		   closedir($handle);
		}


		//chmod('script.py', 0777);
		exec('python3 /var/www/html/demo/php/TvScripts/script.py' , $out);
		//print_r($out);

		//Istruzione fondamentale, rende il file creato accessibile in scrittura e di conseguenza eliminabile nel momento
		//in cui diventa obsoleto, NON LEVARE MAI.
		chmod($completeFile,0777);
		


		if(file_exists($completeFile)){
			//Usiamo il file creato
			$file = $completeFile;

		}else{
			echo"QUALCOSA E' ANDATO STORTO";
		}

	}



	$file = $completeFile;
	//echo"il nome del file e:";
	//print($file);


	//echo"siamo qua";
	$rows = file($file);

	//print_r($rows);


	//print($text);
	if(stripos($text , 'con ')!==false){
		//$personaggioRichiesto= true;
		$personaggioRichiesto = explode('con ', $text);

		//print($personaggioRichiesto[1]);
	}


	//okay, file riempito, devo processare rows


	//Avvaloriamo la variabile locale richiesta mediante parametro avvalorato da dialogflow

	//print_r($parameters);
	foreach ($parameters as $key => $value) {


		switch ($key) {

			case 'TipologiaProgramma':

				if(isset($value) && $value!=""){

					//print("Ho riconosciuto il valore: ".$value);
					$tipologiaRichiesta = $value;
					break;
				}

			case 'GenereProgramma':
				if(isset($value) && $value != ""){

					//print("Ho riconosciuto il valore:".$value);
					$genereRichiesto = $value;
					break;
				}

			case 'CanaleProgramma':
				if(isset($value) && $value != ""){
					//print("Ho riconosciuto il valore:".$value);
					$canaleRichiesto = $value;

				}		
				
				
			
			default:
				# code...
				break;

		}//fine switch


		
	}//fine foreach



/*	Per processare ricorda che gli indicis sono:
		[0] => ﻿NomeCanale
		[1] => Titolo
		[2] => Tipo
		[3] => Genere
		[4] => Attori //Può essere vuoto, controllalo
		[5] => orario*/



		

		foreach ($rows as $key=>$row) {



			if(isset($tipologiaRichiesta) && isset($genereRichiesto)){
				//echo"tipologia e genere settati";
				//print($tipologiaRichiesta);
				//print($genereRichiesto);
				//é solo per telefilm,serie tv  e film questa combinazione, e forse lo sarà per approfondimento
				//print($row);
				//echo" e dopo?";
				if($tipologiaRichiesta == 'Telefilm' || $tipologiaRichiesta == 'Serie Tv'){
//					echo"vai qui?";

					$tipologiaRichiesta = 'Telefilm';

					if(stripos($row , $tipologiaRichiesta)!== false && stripos($row, $genereRichiesto) !== false){

						$result = explode(';', $row);
						//echo"Combinazione telefilm-genere";
						return "Ecco il contenuto che mi hai richiesto:<br>Su ".$result[0]." di genere: ".$result[3]." andrà in onda ".$result[1]." alle ore ".$result[5];
						break;

					}
					//echo"ti incastri?";
				}else if($tipologiaRichiesta == 'Film'){
					//$tipologiaRichiesta = '(FILM)'; Questo causava IL BUG SUPREMO, poichè tipologia richiesta non veniva più riconosciuta = 'Film'

					//in tipologia semplice e genere semplice, controllare se magari sta l'attore
					//echo"La tipologia è stata messa a (FILM) ";
					if(isset($personaggioRichiesto)){
						//echo" non mi dire";
						#Tripla combo, evitare assolutamente, i dati non sono così vasti e quasi sempre sarebbe una query vuota

					}else{
						//echo"siamo entrati";
						//print($tipologiaRichiesta);
						//print($genereRichiesto);
						if(stripos($row , '(FILM)') !== false && stripos($row, $genereRichiesto) !== false){

							$result = explode(';', $row);
							//echo"Combinazione film-genere";
							return"Ecco il contenuto che mi hai richiesto:<br>Su ".$result[0]." di genere: ".$result[3]." andrà in onda ".$result[1]." alle ore ".$result[5];
							//break;

						}
						//echo" lo fa il continue";
						continue;

					}


				}
				//echo" fa questo conitnue esterno";
				continue;

			}else if(isset($tipologiaRichiesta)){
				//SOLO TIPOLOGIA

				//echo"solo tipologia";
				//disambigua film e telefilm:
				if($tipologiaRichiesta == 'Film'){
					$tipologiaRichiesta ='(film)'; //PROBABILE BUG #TODO
				}else if($tipologiaRichiesta == 'Telefilm' || $tipologiaRichiesta == 'Serie Tv'){
					$tipologiaRichiesta = 'Telefilm';
				}
				//tutti gli altri casi vanno in automatico
				if(isset($personaggioRichiesto)){

					if(stripos($row, $tipologiaRichiesta) !== false && stripos($row, $personaggioRichiesto[1])){
						$result = explode(';', $row);

						//echo"Combinazione tipologia e personaggioRichiesto";

						//print_r($result);
						return "Ecco il contenuto che mi hai richiesto:<br>Su ".$result[0]." di genere: ".$result[3]." andrà in onda ".$result[1]." alle ore ".$result[5];
						break;
					}

				}else{ //caso base senza personaggio

					if(stripos($row , $tipologiaRichiesta) !== false){
						$result = explode(';', $row);
						return "Ecco il contenuto che mi hai richiesto:<br>Su ".$result[0]." di genere: ".$result[3]." andrà in onda ".$result[1]." alle ore ".$result[5];
						//break;

					}


				}



				//break;


			}else if(isset($genereRichiesto)){
				//echo"solo genere";


				if(stripos($row , $genereRichiesto) !== false){
					$result = explode(';', $row);
					//return $result;
					return "Ecco il contenuto che mi hai richiesto:<br>Su ".$result[0]." di genere: ".$result[3]." andrà in onda ".$result[1]." alle ore ".$result[5];
					
					break;

				}

				//break;
			}else if(isset($canaleRichiesto)){
				//echo"solo canale";
				//echo"pre ciclo";
				//print($canaleRichiesto);
				
				//come lo implementiamo? leggiamo tutte le righe fino a quando la chiave non viene superata?
				if(stripos($row, $canaleRichiesto)!== false){
					//echo"in ciclo";
					$result = explode(';', $row);
					$risposta .= "Alle ".$result[5]."va in onda ".$result[1].'.<br>';
					$finali = $risposta;

					//per l'ultimo canale
					if($key == count($rows) -1){
						//print("ultima riga");
						return $finali;
					}

					continue;
				}else{
					if(isset($finali)){
						return $finali;
					}else{
						continue;
					}
					
				}
				//break;
			}else if(isset($personaggioRichiesto)){


				//controllo diretto sul personaggio richiesto in ogni riga, evitare assolutamente le combinazioni troppo aleatorie
				//nel caso in cui 
				if(stripos($row, $personaggioRichiesto[1])!== false){
					$result = explode(';', $row);
					return "Ecco un contenuto con ".$result[4]." come mi hai chiesto.<br>Stasera alle ".$result[5]." su ".$result[0]." va in onda ".$result[1]." ".$result[2];
					break;
				}

				//echo "solo personaggio";
				

			}

			continue;
		}//Fine righe

		//Se sei arrivato qui, non so la risposta:

		return "Mi dispiace, il nostro servizio si basa esclusivamente sul palinsesto odierno, non trovo il contenuto da te richiesto";





//Se sei arrivato in questo punto, non ci sono corripspondenze, sorry
//echo "Non ho trovato corrispondenza,mi dispiace";


	
}//fine retriveTV





function recommendTV($resp,$parameters,$text,$email)
{


//Su cosa basiamo la raccomandazione?
/*	Sicuramente controlliamo gli interessi(leggi in iterest -> value) e facciamo un match di parole
	poi possiamo controllare l'umore se sei in un umore negativo, ti consiglio un programma di svago per tirarti su.*/



	$tipologiaRichiesta = null;
	$genereRichiesto = null;

	$result = null;
	$mood = 'neutralità';
	$today = date("d-m-Y");



	//Gestione di avvio dello script

	//Diamo per assodato che il file sia già generato e lo apriamo

	$nomefile = date('d-m-Y');
	$nomefile = $nomefile.".csv";

	$path = '/var/www/html/demo/php/TvScripts/';
	$completeFile = $path.$nomefile;

	if(file_exists($completeFile)){
		//Usiamo quello esistente
		//echo"esiste";
		$file = $completeFile;

	}else{
		//eliminiamo i file vecchi nella cartella TvScript e lanciamo lo script per generare il nuovo file

		//eliminazione dei file

		$percorso = $path;
		if ($handle = opendir($percorso))
		{
		   while (false !== ($file = readdir($handle)))
		   {
		       //echo "$file\n";
		       $res = explode('.', $file);
		       if($res[1] == 'csv' || $res[1] == 'CSV'){
		       	chmod($percorso.$file, 0777);
		       	unlink($percorso.$file);
		       }
		      
		   }
		   closedir($handle);
		}



		//chmod('script.py', 0777);
		exec('python3 /var/www/html/demo/php/TvScripts/script.py' , $out);
		//print_r($out);
		//Istruzione fondamentale per motivi spiegati sopra
		chmod($completeFile,0777);
		


		if(file_exists($completeFile)){
			//Usiamo il file creato
			$file = $completeFile;

		}else{
			echo"QUALCOSA E' ANDATO STORTO";
		}

	}







	$file = $completeFile;
	$rows = file($file);
	//echo count($rows);

	$randomRow = rand(1,count($rows));

	//print($rows[1]);

	$arrayInteressi = getInterestsList($email);
	//interessi($resp,$parameters,$email);

	$mood = getTodayEmotion($today,$email);
	//print_r($arrayInteressi);

	//print_r($rows);
	/*	Per processare ricorda che gli indicis sono:
		[0] => ﻿NomeCanale
		[1] => Titolo
		[2] => Tipo
		[3] => Genere
		[4] => Attori //Può essere vuoto, controllalo
		[5] => orario*/
		$interesse ="";
		$result = "";

		foreach ($rows as $key => $rowlet) {
			
			$result = explode(';', $rowlet);
			//print_r($result);
			$genere = $result[3];
			//print($genere);
			//echo$genere."     ";
			foreach ($arrayInteressi as $interesse => $indice) {
				//echo "coppia ".$genere."---".$interesse." ";
				if(stripos($genere, $interesse) !== false){
				 //match trovato tra genere programma e interesse
					//echo "coppia ".$genere."---".$interesse;
					$spiegazione = 'Ti consiglio questo programma poichè il genere '.$interesse.' rientra nei tuoi interessi';
					//print($genere);
					//print($interesse);
					$raccomandazione = 'Ti consiglio:<br>Su '.$result[0]." alle ore ".$result[5]." va in onda ".$result[1]." ".$result[2]." ".$result[3];
					return array("explain" => $spiegazione, "result" => $raccomandazione);

				}


			}



		}//Fine scansione righe per raccomandazione basata su interessi







	foreach ($rows as $row){

		if($mood == 'gioia' || $mood == 'sorpresa' || $mood == 'neutralità'){

			
			//Consiglia un qualsiasi programma, mentalità positiva
			$result = explode(';', $rows[$randomRow]);
			$spiegazione = "Ti consiglio questo programma perchè sei in uno stato emotivo positivo di ".$mood." e qualsiasi programma va bene.";
			$raccomandazione = 'Ti consiglio:<br>Su '.$result[0]." alle ore".$result[5]." va in onda ".$result[1]." ".$result[2]." ".$result[3];
			return array("explain" => $spiegazione, "result" => $raccomandazione);



		}else if($mood == 'tristezza' || $mood == 'disgusto' || $mood == 'paura'){

			//Consigliamo un film della tipologia svago, oppure dell categoria commedia oppure della categoria satira
			//per far migliorare l'umore

			if(stripos($row, '(SVAGO)') !== false || stripos($row, 'Satira') !== false || stripos($row, 'Commedia') !== false){


				$result = explode(';', $row);
				$spiegazione = "Ti consiglio questo programma perchè sei in uno stato emotivo di ".$mood." e un un po' di svago può aiutarti.";
				$raccomandazione = 'Ti consiglio:<br>Su '.$result[0]." alle ore".$result[5]." va in onda ".$result[1]." ".$result[2]." ".$result[3];
				return array("explain" => $spiegazione, "result" => $raccomandazione);


			}







		}





	}//Fine processo delle righe



//print($arrayInteressi);




}



function recommendTVGuest($resp,$parameters,$text,$email)
{


	$today = date("d-m-Y");

	//Gestione di avvio dello script

	//Diamo per assodato che il file sia già generato e lo apriamo

	$nomefile = date('d-m-Y');
	$nomefile = $nomefile.".csv";

	$path = '/var/www/html/demo/php/TvScripts/';
	$completeFile = $path.$nomefile;

	if(file_exists($completeFile)){
		//Usiamo quello esistente
		//echo"esiste";
		$file = $completeFile;

	}else{
		//eliminiamo i file vecchi nella cartella TvScript e lanciamo lo script per generare il nuovo file

		//eliminazione dei file

		$percorso = $path;
		if ($handle = opendir($percorso))
		{
		   while (false !== ($file = readdir($handle)))
		   {
		       //echo "$file\n";
		       $res = explode('.', $file);
		       if($res[1] == 'csv' || $res[1] == 'CSV'){
		       	chmod($percorso.$file, 0777);
		       	unlink($percorso.$file);
		       }
		      
		   }
		   closedir($handle);
		}



		//chmod('script.py', 0777);
		exec('python3 /var/www/html/demo/php/TvScripts/script.py' , $out);
		//print_r($out);
		//Istruzione fondamentale per motivi spiegati sopra
		chmod($completeFile,0777);
		


		if(file_exists($completeFile)){
			//Usiamo il file creato
			$file = $completeFile;

		}else{
			echo"QUALCOSA E' ANDATO STORTO";
		}

	}

	$file = $completeFile;
	$rows = file($file);
	//echo count($rows);

	$canaleSperimentazione = 'Italia 1';
	$risposta="Puoi guardare Italia 1:<br>";


	foreach ($rows as $key=>$row) {

		if(stripos($row, $canaleSperimentazione)!== false){
			//echo"in ciclo";
			$result = explode(';', $row);
			$risposta .= "Alle ".$result[5]."va in onda ".$result[1].'.<br>';
			$finali = $risposta;

			//per l'ultimo canale
			if($key == count($rows) -1){
				//print("ultima riga");
				return $finali;
			}
			continue;
		}else{
			if(isset($finali)){
				return $finali;
			}else{
				continue;
			}
			
		}

	}//fine ciclo scorrimento righe










}













?>