/**
 * @name     wp-player
 * @desc     初始化播放器。
 * @depend   jQuery, SoundManager2
 * @author   M.J
 * @date     2014-12-21
 * @update   2014-12-24
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * @reutn    {jQuery}
 * @version  2.0.1
 * 
 */
~function($, soundManager){

	var WPPlayer = function(elem, options){

		soundManager.setup({ url: options.swf, debugMode: false });
		
		this.index = 0;
		this.IE6 = !-[1,] && !window.XMLHttpRequest;
		this.url = options.url;
		this.nonce = options.nonce;
		this.single = options.single;

		this.getDOM( $(elem) )
		    .getAttr()
		    .init();
	};

	WPPlayer.prototype = {

		// 初始化
		init: function(){
			var attr = this.attr;
			( typeof attr.xiami !== 'undefined' && $.isNumeric( attr.xiami ) ) ? this.xiamiAction() : this.localAction();
			return this;
		},

		// 虾米类型操作
		xiamiAction: function(){
			var type = typeof this.attr.type == 'undefined' ? 'song' : this.attr.type,
			    xiami = this.attr.xiami,
			    _this = this;

			$.ajax({
				url:this.url,
				headers: { nonce: this.nonce },
				type: 'post',
				data: { action: 'wp_player', type: type, id: xiami },
				dataType: 'json',
				success: function( data ){
					if ( data.code > 0 && data.data.length > 0 ){
						_this.data = data.data;
						_this.createList()
						     .createSound()
						     .addEvent();
					} else {
						_this.getSinaApi();
					}
				}
			});
		},
		
		// 如果主机抓取失败，采用新浪云
		getSinaApi: function(){
			var type = typeof this.attr.type == 'undefined' ? 'song' : this.attr.type,
			    xiami = this.attr.xiami,
			    _this = this;

			$.ajax({
				url:'http://wpplayer.sinaapp.com/',
				type: 'get',
				data: { act: type, id: xiami },
				jsonp: 'callback',
				dataType: 'jsonp',
				success: function( data ){
					if ( data.code > 0 && data.data.length > 0 ){
						_this.data = data.data;
						_this.createList()
						     .createSound()
						     .addEvent();
					}
				}
			});
		},

		// 本地上传操作
		localAction: function(){
			if (typeof this.attr.address === 'undefined'){
				return false;
			}
			var data = {
				title: this.attr.title,
				artist: this.attr.author,
				location: this.attr.address,
				pic: this.attr.thumb
			};
			this.data = [data];
			this.createList()
			     .createSound()
			     .addEvent();
		},

		// 创建音乐列表
		createList: function(){
			var i = 0,
			    tpl = '',
			    DOM = this.DOM,
			    data = this.data;
			    len = data.length;

			for ( ; i<len; i++ ){
				var odd = i % 2 ? 'odd' : '';
				tpl += WPPlayer.template
						.replace('{i}', i)
						.replace('{class}', odd)
						.replace('{author}', data[i].artist)
						.replace('{serial}', i+1)
						.replace('{title}', data[i].title);
			}

			$(tpl).appendTo(DOM.list.children('ul')).first().addClass('current');

			return this;
		},

		// 创建声音
		createSound: function(val){
			var _this = this,
			    index = (typeof val === 'undefined') ? 0 : val,
			    data = this.data[index],
			    DOM = this.DOM,
			    autoplay = ( this.single == 'true' && this.attr.autoplay == "1" ) ? true : false;

			//setting DOM
			DOM.title.text(data.title);
			DOM.author.text(data.artist);
			DOM.thumb.children('img').attr('src', data.pic);

			soundManager.onready(function() {
				if ( typeof _this.sound === 'object' ) _this.sound.destruct();

				_this.timeReady = false;

				//create sound
				_this.sound = soundManager.createSound({
						url: data.location,
						onload: function(){
							_this.timeReady = true;
						},
						onplay: function(){ _this.setPlay() },
						onresume: function(){ _this.setPlay() },
						onpause: function(){ _this.setStop() },
						onfinish: function(){ _this.nextSound() },
						whileplaying: function(){	
							var count, minute, second, pre,
							    position = (this.position / this.duration)*100,
							    playbar = position > 100 ? '100%' : position.toFixed(5) + '%';

							if ( _this.timeReady ) {
								pre = '-';
								count = Math.floor((this.duration - this.position) / 1000);
								minute = _this.formatNumber( Math.floor( count / 60 ) );
								second = _this.formatNumber( Math.floor( count % 60 ) );
							} else {
								pre = '';
								minute = '00';
								second = '00';
							}

							DOM.playbar.width(playbar);
							DOM.time.text( pre + minute +':'+ second );
						},
						whileloading: function(){
							var seekbar = this.bytesTotal ? ( this.bytesLoaded / this.bytesTotal ) * 100 : 100;
							DOM.seekbar.width(seekbar+'%');
						}
					});


				_this.soundEvent();

				if ( typeof val !== 'undefined' || autoplay ) _this.sound.play();

			});

			return this;
		},

		//播放器事件
		addEvent: function(){
			var DOM = this.DOM,
			    _this = this;

			//showList
			DOM.wrap.on('click', 'div.wp-player-list-btn', function(){
				var has = $(this).hasClass('wp-player-open');
				$(this)[has ? 'removeClass' : 'addClass']('wp-player-open');
				DOM.list.stop(true,true)[has ? 'slideDown': 'slideUp']('fast');
			});

			// list select
			DOM.list.on('click', 'li', function(){
				var index = parseInt( $(this).attr('data-index'), 10),
				    has = $(this).hasClass('current') && _this.sound.playState > 0;
				( index < 0 || index > _this.data.length-1 ) ? _this.index = 0 : _this.index = index;

				if ( has && !_this.sound.paused ){
					_this.sound.pause();
				} else if ( has && _this.sound.paused ){
					_this.sound.resume()
				} else {
					_this.reset().setList().createSound(_this.index);
				}
			});

			return this;
		},

		// SoundManage Event
		soundEvent: function(){
			var DOM = this.DOM,
			    _this = this;

			//sound play
			DOM.seekbar.off().on('click', function(event){ _this.seekbar(event) });
			DOM.play.off().on('click', function(){ _this.play() });
			DOM.stop.off().on('click', function(){ _this.stop() });

			//prev, next
			if ( this.data.length > 2 ){
				DOM.previous.off().on('click', function(){ _this.prevSound() });
				DOM.next.off().on('click', function(){ _this.nextSound() });
			}

		},

		// 播放进度 Event
		seekbar: function(event){
			var DOM = this.DOM,
			    _x = event.offsetX ? event.offsetX : (event.clientX - DOM.progress.offset().left).toFixed(0);
			var offsetX = ( _x / DOM.progress.width() ) * this.sound.duration;
			if ( offsetX < 0 ) offsetX = 0;
			if ( offsetX > this.sound.duration ) offsetX = this.sound.duration;
			this.sound.setPosition(offsetX);
		},

		//播放 Event
		play: function(){
			this.sound[this.sound.playState < 1 ? 'play' : 'resume']();
		},

		//暂停 Event
		stop: function(){
			this.sound.pause();
		},

		// 上一首 Event
		prevSound: function(){
			var minIndex = 0;
			if ( --this.index < minIndex ) this.index = this.data.length-1;
			this.reset().setList().createSound(this.index);
		},

		// 下一首 Event
		nextSound: function(){
			var maxIndex = this.data.length-1;
			if ( ++this.index > maxIndex ) this.index = 0;
			this.reset().setList().createSound(this.index);
		},

		// 设置当前播放状态
		setPlay: function(){
			var DOM = this.DOM;
			DOM.playing.stop(true,true)[this.IE6 ? 'show' : 'fadeIn']();
			DOM.play.hide();
			DOM.stop.show();
			return this;
		},

		// 设置当前暂停状态
		setStop: function(){
			var DOM = this.DOM;
			DOM.playing.stop(true,true)[this.IE6 ? 'hide' : 'fadeOut']();
			DOM.play.show();
			DOM.stop.hide();
			return this;
		},

		// 重置播放器界面
		reset: function(){
			var DOM = this.DOM;
			this.setStop();
			DOM.seekbar.width(0);
			DOM.playbar.width(0);
			return this;
		},

		//设置列表选中
		setList: function(){
			var DOM = this.DOM;
			DOM.list.find('li').removeClass('current').eq(this.index).addClass('current');
			return this;
		},

		// 获取播放器DOM
		getDOM: function($elem){
			var elem = $elem[0].getElementsByTagName('*'),
			    DOM = {};

			DOM['wrap'] = $elem;
			for (var i = 0; i < elem.length; i++){
				if ( elem[i].className.indexOf('wp-player') > -1 ){
					var name = elem[i].className.replace('wp-player', '').replace(/-/g, '');
					DOM[name] = $(elem[i]);
				}
			}

			this.DOM = DOM;
			return this;
		},

		formatNumber: function(val){
			var str = val < 0 ? '0' : val.toString();
			if (str.length > 1){
				return str;
			}
			return '0' + str;
		},

		//获取播放器必须的属性
		getAttr: function(){
			var DOM = this.DOM;
			this.attr = {
				type: DOM.wrap.attr('data-type'),
				xiami: DOM.wrap.attr('data-xiami'),
				title: DOM.wrap.attr('data-title'),
				author: DOM.wrap.attr('data-author'),
				address: DOM.wrap.attr('data-address'),
				thumb: DOM.wrap.attr('data-thumb'),
				autoplay: DOM.wrap.attr('data-autoplay')
			};
			return this;
		}
	};

	// 列表模板
	WPPlayer.template = '<li data-index="{i}" class="{class}"><a href="javascript:void(0);"><span class="wp-player-list-author">{author}</span><span class="wp-player-list-order">{serial}</span><span class="wp-player-list-title">{title}</span></a></li>';

	// 扩展 jQuery 对象
	$.fn.WPPlayer = function( options ){
		return this.each(function(){
			new WPPlayer( this, options );
		});
	};

	return $;
}(jQuery, soundManager);

jQuery('[data-wp-player="wp-player"]').WPPlayer( wp_player_params );