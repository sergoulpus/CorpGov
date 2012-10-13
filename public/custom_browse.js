$(function() { 

	var theTable = $('#firmaMainView');

	theTable.find("tbody > tr").find("td:eq(1)").mousedown(function(){
		$(this).prev().find(":checkbox").click()
	});

	$.uiTableFilter(theTable, $('#filter').val());

	$("#filter").keyup(function() {
		$.uiTableFilter( theTable, this.value );
					 
		$('#refresh').attr('href','?filter='+this.value);
	})

	$('#filter-form').submit(function(){
		theTable.find("tbody > tr:visible > td:eq(1)").mousedown();
		return false;
	}).focus(); //Give focus to input field


	$('#clear').click(function(){
		event.preventDefault();
		$('#filter').attr('value','');
		$('#filter').keyup();
								
	});


});