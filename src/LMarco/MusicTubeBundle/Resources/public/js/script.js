/* Author: Marco Leong

*/

function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}
$(document).ready(function(){
	$("#aboutThis").modal({
		show: false
	});

	$('#music-create-form').ajaxForm({
		//success call back here
		dataType: "json",
		success: function(data, status, xhr, form){
			//fire start download request;
			var _data = data;
			if(data.progress === 'CONVERTED'){
				var doneProgressBar = '<div  class="progress progress-success progress-striped active" id="progress-bar-'+_data.videoId+'"> <div class="bar" style=" width: 100%;">Done</div></div>';
					// create an progree bar with specific id.
				$('#send-button').hide('fast');
				$('#music-create-form').html(doneProgressBar);

				//show download button
				var download_button = $("#download-button");
				$.getJSON(Routing.generate('music_get_download_link', {videoId:_data.videoId}), function(data){
					download_button.attr({"href": data.url });
					download_button.show('slow');
				});
						
			}else{
				$.get(Routing.generate('music_start_download', { id: data.videoId } ));

				var getProgLink = Routing.generate('music_get_progress', { jobId: data.videoId });

				var musicProgress = '';
				$.get(getProgLink, function(progress){
					if(musicProgress === 'CONVERTED' || progress == 100){
						var doneProgressBar = '<div  class="progress progress-success progress-striped active" id="progress-bar-'+_data.videoId+'"> <div class="bar" style=" width: 100%;">Done</div></div>';
						// create an progree bar with specific id.
						$('#send-button').hide('fast');
						$('#music-create-form').html(doneProgressBar);
						//show download button
						var download_button = $("#download-button");
						$.getJSON(Routing.generate('music_get_download_link', {videoId:_data.videoId}), function(data){
							download_button.attr({"href": data.url });
							download_button.show('slow');
						});
					}else{
						//bind request progress request

						var progressBar = '<div  class="progress progress-success progress-striped active" id="progress-bar-'+_data.videoId+'"> <div class="bar" style="width: 0%;"></div></div>';
						// create an progree bar with specific id.
						$('#send-button').hide('fast');
						$('#music-create-form').html(progressBar);

						// the progress bar
						var progressBarRef = $('#progress-bar-'+_data.videoId +' > div');
						var setProgress = function(){
							var progressBarRef = $('#progress-bar-'+_data.videoId +' > div');

							$.get(getProgLink, function(data){
								if(data.indexOf("ERR" < 0)){
									if(data == 100){
										$(progressBarRef).css("width", data +"%");
										$(progressBarRef).text('DONE');

										var download_button = $("#download-button");
										$.getJSON(Routing.generate('music_get_download_link', {videoId:_data.videoId}), function(data){
											if(data.file_status == 'NOT_READY'){
												sleep(2000);
											}else{
												download_button.attr({"href": data.url });
												download_button.show('slow');
											}
											
										});
										window.clearInterval();

										//wait 10 sec, unhide download button.


									}else{
										$(progressBarRef).css("width", data +"%");
										$(progressBarRef).text(data + "%");
									}
								}else{
									window.clearInterval();
								}
								
							});
						};
						window.setInterval(setProgress, 1000);
					}
				});
			}
			
		}
	});
});
