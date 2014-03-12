jQuery(function($) {
  $('#recipressextend-print-link a').click(function(e) {
    var recipeSrc = $(this).attr('src');
    
    $('#printarea').load(function(){
      this.contentWindow.print();
      $(this).unbind('load');
    }).attr('src', recipeSrc);
    
    e.preventDefault();
  });
  
});
