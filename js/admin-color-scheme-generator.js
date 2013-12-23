jQuery(function($) {
	$('.acsg-colorpicker').minicolors();

	$('#acsg_form input[type=submit]').click(function(e) {
		if ($('#scheme_name').val() == '') { alert('Please enter a color scheme name'); return false; }
		else if ($('#scheme_author').val() == '') { alert('Please enter a color scheme author'); return false; }
		else if ($('#scheme_base_color').val() == '') { alert('Please choose a base color'); return false; }
		else if ($('#scheme_highlight_color').val() == '') { alert('Please choose a highlight color'); return false; }
		else if ($('#scheme_notification_color').val() == '') { alert('Please choose a notification color'); return false; }
		else if ($('#scheme_action_color').val() == '') { alert('Please choose an action color'); return false; }
	});
});