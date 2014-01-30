$(document).ready(function() {
	$('body').on('click', 'a.grid-ajax', function(e) {
		e.preventDefault();
		$.get($(this).attr('href'));
	});
	
	$('body').on('click', 'a.btn-load-exceptions, a.btn-project-scan', function() {
		$(this).removeClass('btn-danger');
		$(this).addClass('btn-warning');
		$(this).html('Updating...');
	});
	
	$("a.ajax").live("click", function(event) {
		event.preventDefault();
		$.get(this.href);
	});
	
	$("form.ajax").live("submit", function() {
		$(this).ajaxSubmit();
		return false;
	});
	
	$("form.ajax :submit").live("click", function() {
		$(this).ajaxSubmit();
		return false;
	});
});