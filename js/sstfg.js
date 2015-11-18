jQuery(document).ready(function($) {
	if( document.getElementById('manual').checked ) {
		$('.sstfg_ticket_access_regularity').fadeTo("slow",0.5);
		$('.sstfg_ticket_access_regularity input').prop("disabled",true);
	}
	$('.sstfg_ticket_access_mode input').on('click', function(e) {
		if( document.getElementById('manual').checked ) {
			$('.sstfg_ticket_access_regularity').fadeTo("slow",0.5);
			$('.sstfg_ticket_access_regularity input').prop("disabled",true);
		} else {
			$('.sstfg_ticket_access_regularity').fadeTo("fast",1);
			$('.sstfg_ticket_access_regularity input').prop("disabled",false);
			
		}
	});
});
