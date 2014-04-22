(function($){
  google.maps.event.addDomListener(window, 'load', initialize);

  function initialize(){
    var map_elems = $('.timby-thumb-map');

    $.each(map_elems, function(){
      var loc = {
        lat : parseFloat($(this).attr('data-lat')),
        lng : parseFloat($(this).attr('data-lng'))
      };

      // initialize map only if the element exists in the DOM
      var map = new google.maps.Map(
        $(this)[0],
        {
          zoom: 7,
          center: new google.maps.LatLng(
            loc.lat,
            loc.lng
          )
        }
      );


      new google.maps.Marker({
        position: new google.maps.LatLng(
          loc.lat,
          loc.lng
        ),
        map: map
      });  

    })
  }
  


})(jQuery)