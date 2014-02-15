function loadAudioPlayer( params ) {
	jQuery(".wp-player-audio").each(function() {
	
		//Default var
		var _this = jQuery(this),
			$audio_id = _this.find(".entry-music").attr("data-audio-id"),
			$media = _this.find(".entry-music").attr("data-audio-file");
			
			
		//wp-player Options
		var opt = {
			audio_id : $audio_id,
			play_id : '#jp_jplayer_'+$audio_id,
			play_ancestor : '#jp_container_'+$audio_id,
			media : $media, 
			xiami : _this.find(".entry-music").attr("data-audio-xiami"),
			loop : parseInt( _this.find(".entry-music").attr("data-audio-loop") ),
			autoPlay : parseInt( _this.find(".entry-music").attr("data-audio-autoPlay") ),
			extension : $media.split('.').pop(),
			domain : params.domain,
			is_single : params.is_single,
			api : params.api,
			themes : _this.find(".entry-music").attr("data-audio-themes")
		};
		
		if ( opt.xiami != "" ){
			jQuery.ajax({ 
				type: "get", 
				url: "http://" + opt.api, 
				async:false,
				dataType: "jsonp",
				data:{
					act:'getsong',
					id:opt.xiami
				},
				success : function(json){  
					if ( json.stats == 1 ){
						_this.find('.music-author').children('img').attr( 'src', json.pic );
						_this.find('.music-author').children('img').attr( 'title', json.title );
						_this.find('.music-author').children('img').attr( 'alt', json.title );
						_this.find('.music-text').children('h3').text( json.title );
						_this.find('.music-text').children('p').text( json.author );
						opt.media = json.location;
						opt.extension = 'mp3';
						wp_player( opt );
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) { 
						alert(errorThrown); 
				} 
			});
		} else { 
			if ( opt.extension.toLowerCase() =='mp3' ) {
				opt.extension = 'mp3';
			} else if ( opt.extension.toLowerCase() =='mp4' ||  opt.extension.toLowerCase() =='m4a' ) {
				opt.extension = 'm4a';
			} else if ( opt.extension.toLowerCase() =='ogg' || opt.extension.toLowerCase() =='oga' ) {
				opt.extension = 'oga';
			} else {
				opt.extension = '';
			}
			wp_player( opt );
		}
	});
}

function wp_player( opt ){
	if ( !opt ) return false;
	var extension = opt.extension,
		themes = opt.themes,
		ancestor = opt.play_ancestor;
	jQuery(opt.play_id).jPlayer({
		ready: function (event) {
			var playerOptions = {
				extension : opt.media
			};
			playerOptions[extension] = opt.media;
			if ( opt.is_single ){
				if ( opt.autoPlay ){
					jQuery(this).jPlayer("setMedia", playerOptions).jPlayer('play');
				} else {
					jQuery(this).jPlayer("setMedia", playerOptions);
				}
			} else {
				jQuery(this).jPlayer("setMedia", playerOptions);
			}
		},
		loop:opt.loop,
		swfPath: opt.domain,
		supplied: opt.extension,
		wmode: 'window',
		errorAlerts:true,
		cssSelectorAncestor: opt.play_ancestor,
		play:function(){
			if ( themes == 2 ){
				jQuery(ancestor+' .music-author').children('img').addClass('runing');
			}
		},
		pause:function(){
			if ( themes == 2 ){
				jQuery(ancestor+' .music-author').children('img').removeClass('runing');
			}
		}
	});
}
loadAudioPlayer( wp_player_params );