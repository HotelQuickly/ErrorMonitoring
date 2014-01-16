$(document).ready(function() {
   $('body').on('click', 'a.btn-solve.grid-ajax', function(e) {
       e.preventDefault();
       var href = $(this).attr('href');
       $(this).closest('tr').fadeOut(function() {
           $.get(href);
       });
   });
});