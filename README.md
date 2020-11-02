# MyrrorBotRepo
La pagina index.html contiene la schermata di login e permette di accedere
alla pagina principale chatbot.html,l'accesso è basato sulla creazione di un
cookie da parte della pagine index.html.
la pagina chatbot.html tramite script.js controlla che il cookie sia presente.
In locale la lettura del cookie non funziona correttamente perciò bisogna seguire
le seguenti indicazioni.

SE SI VUOLE ESEGUIRE IL SITO IN LOCALE
1) commentare in script.js tutte le seguenti righe
window.location.href = 'index.html';

2)impostare in script js la variabile email staticamente
var email = "cat@cat.it" alla riga 6 e alla riga 105.

3)In readLocalJson.php impostare staticamente la mail
Es.  $email = "cat@cat.it"; 

4)Assicurarsi di avere in locale nella cartella fileMyrror il
file corrispondente alla propria email, es se l'account è cat@cat.it
bisogna scaricare il file past_cat@cat.it, se non esiste basterà
effettuare sul server il login con le proprie credenziali e scaricarlo 
con fileZilla dalla cartella fileMyrror

5)Accedere direttamente alla pagina chatbot.html senza effettuare
il login


ARCHITETTURA DEL SISTEMA
La pagina script.js si occupa della visualizzazione delle risposte,legge il testo
scritto dall'utente ed interroga il modulo intentDetection.php che è la componente
centrale della parte server.
IntentDetection.php include tutti gli altri moduli, ogni modulo gestisce le funzioni 
di una sfaccettatura es.Affects.php o di un servizio es.spotifyIntent.php.
L'intentDetection tramite la funzione detect_intent_texts si collega a dialogflow che come risposta
restituisce il nome dell'intent ed eventuali entità riconosciute all'interno della frase. 

FOOD RECOMMENDER
Nella cartella foodwebapp è presente lo script per avviare il foodrecommender (start_server.sh).
Il foodrecommender è scritto in python e necessita di pandas e flask.

WEBAPP
La webapp si trova nella cartella foodrecsys.
