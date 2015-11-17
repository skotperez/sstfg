jQuery(document).ready(function($) {
	if( document.getElementById('manual').checked ) {
		$('.user_ticket_access_regularity').fadeTo("slow",0.5);
		$('.user_ticket_access_regularity input').prop("disabled",true);
	}
	$('.user_ticket_access_mode input').on('click', function(e) {
		if( document.getElementById('manual').checked ) {
			$('.user_ticket_access_regularity').fadeTo("slow",0.5);
			$('.user_ticket_access_regularity input').prop("disabled",true);
		} else {
			$('.user_ticket_access_regularity').fadeTo("fast",1);
			$('.user_ticket_access_regularity input').prop("disabled",false);
			
		}
	});
});
