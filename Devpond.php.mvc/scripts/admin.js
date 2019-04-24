tinymce.init({ selector:'textarea' });

setInterval(function() {
	loadUsersOnline();
}, 500);

$(document).ready(function()
{
	$('#selectAll').click(function()
		{
			if(this.checked)
			{
				$('.checkBoxes').each(function(){
					this.checked = true;
				});
			} else if (this.checked == false)
			{
				$('.checkBoxes').each(function(){
					this.checked = false;
				});
			}
		});
	
	var wheelLoader = "<div id='load-screen'><div id='loading'></div></div>";
	$("#page-wrapper").prepend(wheelLoader);
	$('#load-screen').delay(700).fadeOut(600, function(){
		$(this).remove();
	})
});

$(function () {
    $('#author_readonly').attr('readonly', 'true'); // mark it as read only
});

function loadUsersOnline() {
	$.ajax({
			url      : '/User/UsersOnline',
			success  : function(Result){
				var $val = $.parseJSON(Result);
				$(".users_online").text($val);
			}
		}
	);
};
