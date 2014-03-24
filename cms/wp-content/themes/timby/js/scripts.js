// Simple JavaScript Templating
// John Resig - http://ejohn.org/ - MIT Licensed
(function(){
  var cache = {};
 
  this.tmpl = function tmpl(str, data){
    // Figure out if we're getting a template, or if we need to
    // load the template - and be sure to cache the result.
    var fn = !/\W/.test(str) ?
      cache[str] = cache[str] ||
        tmpl(document.getElementById(str).innerHTML) :
     
      // Generate a reusable function that will serve as a template
      // generator (and which will be cached).
      new Function("obj",
        "var p=[],print=function(){p.push.apply(p,arguments);};" +
       
        // Introduce the data as local variables using with(){}
        "with(obj){p.push('" +
       
        // Convert the template into pure JavaScript
        str
          .replace(/[\r\t\n]/g, " ")
          .split("<%").join("\t")
          .replace(/((^|%>)[^\t]*)'/g, "$1\r")
          .replace(/\t=(.*?)%>/g, "',$1,'")
          .split("\t").join("');")
          .split("%>").join("p.push('")
          .split("\r").join("\\'")
      + "');}return p.join('');");
   
    // Provide some basic currying to the user
    return data ? fn( data ) : fn;
  };
})();

$(function(){
  var _template_uri = $('body').attr('data-template-uri');

  $('.list-report').on('click', function(){
    var report_id = parseFloat($(this).attr('data-id'));

    $.get(_template_uri +'/ajax.php?action=get_report&id='+report_id, 
      function(response){
        response = $.parseJSON(response);

        if(response.status == 'success'){
          $('.report-wrap').html(
            tmpl("report_template", response.data)
          );

          // initialize map only if the element exists in the DOM
          var map = new google.maps.Map(
            document.getElementById('report-location'),
            {
              zoom: 7,
              center: new google.maps.LatLng(response.data.lat,response.data.lng)
            }
          );

          var marker = new google.maps.Marker({
            position: new google.maps.LatLng(
              response.data.lat,
              response.data.lng
            ),
            map: map
          });

        }
      }
    );
  });

})