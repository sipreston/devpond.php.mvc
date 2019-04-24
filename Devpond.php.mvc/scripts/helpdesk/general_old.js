$(document).ready(function(){
    var wheelLoader = "<div id='load-screen'><div id='loading'></div></div>";
    $("#tableresults").html(wheelLoader);
    // $('#load-screen').delay(700).fadeOut(600, function() {
    //     $(this).remove();
		// });
    loadTickets();

});

function loadTickets() {
	var urlPath = $(location).attr('hostname');
	var url = "helpdesk/index";
	if(urlPath.match("^helpdesk"))
	{
		url = "index";
	}
	$.ajax({
		url: url,
		data: {action: 'results' },
		success: function(result){
			$("#tableresults").html(result);
			updateTotals();
		}
	});
};

function updateTotals() {
	var tkcount = ticketsTotal;
	$("#titleCount").html(tkcount + ' HelpDesk Tickets');
}

function loadFilteredTickets()
{
    var wheelLoader = "<div id='load-screen'><div id='loading'></div></div>";
    $("#tableresults").html(wheelLoader);
	var chkBoxShowClosed = $("#chkBoxShowClosed");
	var showClosed = (chkBoxShowClosed.is(':checked')) ? true : false;
	$.ajax({
		url: 'helpdesk/index',
		data: {action: 'results', show_closed: showClosed},
        success: function(result){
            $("#tableresults").html(result);
            updateTotals();
        }
		}
	)
}

$(function(){

	bindEvents();

	function bindEvents()
	{
		var popup = $("#popupwrapper");

		popup.click(function(){
			hidePopup(popup);
		});

		$('.info-btn').click(function(){
			ticketInfo(popup, $(this).attr('data-ticket'));
		});
	}

	function popupAlreadyShown(popup)
	{
		if(popup.hasClass('popupshow')){
			return true;
		}
		return false;
	}

	function ticketInfo(popup, ticket)
	{
		if(popupAlreadyShown(popup) == true){
			hidePopup(popup);
		} 
		showPopup(popup, ticket);
	}

	function hidePopup(popup)
	{
		popup.removeClass('popupshow');
		popup.addClass('popuphide');
		popup.empty();
	}

	function showPopup(popup, ticket)
	{
		popup.load('helpdesk/index?action=info&ref=' + ticket);
		popup.removeClass('popuphide');
		popup.addClass('popupshow');
	}
});

$(function(){

	bindEvents();
	loadFields();

	function bindEvents(){
		var $filterableRows = $('#issues').find('tr').not(':first');

		$('.search-key').on('input', function () {
			$filterableRows.hide().filter(function() {
		  		return $(this).find('td').filter(function() {
		  			var tdText = $(this).text().toLowerCase(),
		      		inputValue = $('#' + $(this).data('input')).val().toLowerCase();
		      		return tdText.indexOf(inputValue) != -1;
		      	}).length == $(this).find('td').length;
		  	}).show();

		  	updateStoredFieldVal($(this).attr('id'), $(this).val());
		});
	}

	function updateStoredFieldVal($id, $val){
	  	window.localStorage[$id] = $val;
	}

	function loadFields(){
		$('.search-key').each(function(){
			var id = $(this).attr('id');
			if(window.localStorage[id]){
		        $("input[id = '" + id + "']").val(window.localStorage[id]);
		        window.localStorage[id] = '';
		        $(this).trigger('input');
		    }
	    });
	}
});

