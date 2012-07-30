$(document).ready(function(){
	$('.start-download').click(function(){
		$.ajax({
			url: Routing.generate('music_start_download',{id: $(this).data("id") }),
			type: "get"
		});
		$(this).remove();
	});
});