      var marker;
      var geocoder;
      var watch = null;
      var options = {enableHighAccuracy:true, timeout: 35000}; 
      var comune = "";

     function getCity(){
      return comune;
    }

      document.addEventListener("deviceready", onDeviceReady, false);

      $( document ).ready(function() {
          onDeviceReady();
      });

         function onDeviceReady() {    
          
        //geocoder = new google.maps.Geocoder();
         //localizzazione GPS
         watch = navigator.geolocation.watchPosition(onSuccess,onError,options);
       
    }

   
    
function onSuccess(position){
      //geolocalizzazione avvenuta con successo        

      navigator.geolocation.clearWatch(watch);
      var lat = position.coords.latitude;
      var long = position.coords.longitude;
     
      //creo un oggetto posizione
      //var latlng = new google.maps.LatLng(lat, long);
      var latlng = lat +","+long;
      var link = 'http://www.mapquestapi.com/geocoding/v1/reverse?key=pJ4Mo5GOTpHMvuCRlIyiFomZqsSAeAHM&location='+latlng+'&includeRoadMetadata=true&includeNearestIntersection=true'
     // console.log(link);
     $.ajax({
      type : 'GET',
      url: link,
    dataType: 'JSON',
      success: function(response){
       
        console.log(response.results);
        $(response.results).each(function(key,value){
          $(value.locations).each(function(k,v){
            if(v.adminArea5 != ""){
              comune = v.adminArea5;
            }
            //console.log(v.adminArea5);
          })
          
        });
        
      }
      

     });
    }      

        function onError(error){
  /*
   se si verifica un errore durante la geolocalizzazione lo mostro 
  */
  console.log(error);


    }  


      function onrefresh(position){

      navigator.geolocation.clearWatch(watch);
      var latitude = position.coords.latitude;
      var longitude = position.coords.longitude;
    
      //creo un oggetto posizione
      var latlng = new google.maps.LatLng(latitude, longitude);

   
   
  }