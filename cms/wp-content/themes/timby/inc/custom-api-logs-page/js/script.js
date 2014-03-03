jQuery(document).ready(function($) {
  $('.datepicker')
    .datepicker({
       dateFormat : 'd MM, yy',
       maxDate: "today" 
    });
});