jQuery(function($){
  
	$(function() {
		$( '#recipressextend-print-link a' ).click(function(e) {
			e.preventDefault();
			print = window.open( $(this).attr( 'href' ), 'print_win', 'width=1024, height=800, scrollbars=yes' );
		});
	});
  
});
