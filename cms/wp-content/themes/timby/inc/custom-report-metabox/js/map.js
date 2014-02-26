var marker,
    map;

function initialize() {
  var mapOptions = {
    zoom: 8,
    center: new google.maps.LatLng(6.593820, -9.394627)
  };

  // initialize map only if the element exists in the DOM
  if( document.getElementById('map-canvas') ) {
    map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);

    //set marker onload
    if(document.getElementById('_latitude').value){
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(
          document.getElementById('_latitude').value,
          document.getElementById('_longitude').value
        ),
        map: map
      });
    }
    
    //set marker onclick
    google.maps.event.addListener(map, 'click', function(e) {
      if(typeof(marker) !== 'undefined'){
        marker.setMap(null);
      }
      placeMarker(e.latLng, map);
    });

  }
  
}

function placeMarker(position, map) {
  marker = new google.maps.Marker({
    position: position,
    map: map
  });
  
  //set the values in the fields
  document.getElementById('_latitude').value = position.lat();
  document.getElementById('_longitude').value = position.lng();
  
  map.panTo(position);
}

function getlocation(){
  var location_str = jQuery('#_location_address').val();
  var coordinates = jQuery.getJSON('http://maps.googleapis.com/maps/api/geocode/json?address='+location_str+',Liberia&sensor=false', function( data ) {
    latlong = {
      lat: data.results[0].geometry.location.lat,
      lng: data.results[0].geometry.location.lng
    };

    map.setCenter(latlong);
    map.setZoom(13);

    //add a marker to this point
    marker = new google.maps.Marker({
      map: map,
      position: latlong
    });
  });
}

google.maps.event.addDomListener(window, 'load', initialize);