
var debug = false; 

function setDebug( flag){
debug = flag;
}

function isDebugEnabled(){
  return debug;
}

  $("ul.chat").on("click","button.btn-no",function() {

    var timestamp = $(this).attr("id");
    
    $(this).attr("disabled", true);
    timestamp = timestamp.substr(2,timestamp.length);
      
    $("#yes"+timestamp).attr("disabled", true);
    var testo  = $("#hide"+timestamp).text();
    var mail = getEmail();
    var question = $( "#quest"+timestamp ).text();

    var timestampStart = getTimestampStart();
    var timestampEnd = Date.now();
     var flag = $("#flagNews"+timestamp).val();
if(flag != null && flag != undefined){
    rating(testo,question,'no',mail,timestampStart,timestampEnd,flag); 
}else{
    rating(testo,question,'no',mail,timestampStart,timestampEnd,""); 
  
}

  

  });


  $("ul.chat").on("click","button.btn-yes",function() {

    var timestamp = $(this).attr("id");
    $(this).attr("disabled", true);
    var mail = getEmail();
    timestamp = timestamp.substr(3,timestamp.length); 
    
    $("#no"+timestamp).attr("disabled", true);
    var testo  = $("#hide"+timestamp).html();

    var question = $( "#quest"+timestamp ).text();

    var timestampStart = getTimestampStart();
    var timestampEnd = Date.now();

 var flag = $("#flagNews"+timestamp).val();
if(flag != null && flag != undefined){
    rating(testo,question,'si',mail,timestampStart,timestampEnd,flag); 
}else{
    rating(testo,question,'si',mail,timestampStart,timestampEnd,""); 
  
}
 


  });



function rating(testo,question,rate,mail,timestampStart,timestampEnd,flag){

if(flag == ""){

     $.ajax({
        type: "POST",
        url: "php/log.php",
        data: {answer:testo,quest:question,rating:rate,email:mail,timestampStart:timestampStart,timestampEnd:timestampEnd},
        success: function(data) {
            alert(data);
        }
      }); 

}else{

    $.ajax({
        type: "POST",
        url: "php/log.php",
        data: {answer:testo,quest:question,rating:rate,email:mail,timestampStart:timestampStart,timestampEnd:timestampEnd,flag:flag},
        success: function(data) {
            alert(data);
        }
      }); 
}

    
}
      