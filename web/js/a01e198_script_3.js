/* Author:

*/

$(document).ready(function(){
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
			}else{
				$.get(Routing.generate('music_start_download', { id: data.videoId } ));

				var getProgLink = Routing.generate('music_get_progress', { jobId: data.videoId });

				var musicProgress = '';
				$.get(getProgLink, function(progress){
					console.log(progress);
					if(musicProgress === 'CONVERTED'){
						var doneProgressBar = '<div  class="progress progress-success progress-striped active" id="progress-bar-'+_data.videoId+'"> <div class="bar" style=" width: 100%;">Done</div></div>';
						// create an progree bar with specific id.
						$('#send-button').hide('fast');
						$('#music-create-form').html(doneProgressBar);

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
								if(data == '100%'){
									$(progressBarRef).text('Done');
									window.clearInterval();
								}else{
									$(progressBarRef).css("width", data +"%");
									$(progressBarRef).text(data);
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
