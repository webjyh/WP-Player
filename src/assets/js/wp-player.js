/*!
 * @name     wp-player
 * @desc     初始化播放器。
 * @depend   jQuery, SoundManager2
 * @author   M.J
 * @date     2014-12-21
 * @update   <%=date%>
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * @reutn    {jQuery}
 * @version  <%=version%>
 * 
 */
~function($, soundManager) {

    var WPPlayer = function(elem, options) {

        soundManager.setup({ url: options.swf, debugMode: false });

        this.index = 0;                                     //当前播放歌曲
        this.url = options.url;                             //Ajax请求地址
        this.nonce = options.nonce;                         //WordPress 随机数
        this.img = options.img;                             //默认图片
        this.IE6 = !-[1,] && !window.XMLHttpRequest;        //是否是IE 6
        this.single = options.single;                       //是否是详情页
        this.lyric = null;                                  //当前歌曲歌词
        this.offset = {};                                   //歌词滚动最小，最大值
        this.mark = null;                                   //歌词滚动标记
        this.lyricH = 300;                                  //歌词容器高度
        this.line = 27;                                     //歌词单行行高
        this.isLoad = false;                                //获取歌曲信息是否完成
        this.$elem = $(elem);                               //当前元素DOM
        this.first = true;                                  //是否是第一次载入
        this.isMobile = 'createTouch' in document && !('onmousemove' in document) || /(iPhone|iPad|iPod)/i.test(navigator.userAgent);

        this.getDOM(this.$elem).getAttr().init();
    };

    WPPlayer.prototype = {

        // 初始化
        init: function() {
            var attr = this.attr,
                DOM = this.DOM;
            
            DOM.time.text('00:00');
            DOM.title.text('Loading...');
            DOM.author.text('Loading...');
            if (attr.lyric && this.isMobile) DOM.lyricsbtn.hide();
            this[attr.id ? 'actions' : 'localAction']();
        },

        // 音乐获取操作
        actions: function() {
            var _this = this;

            $.ajax({
                url: this.url,
                type: 'post',
                headers: {
                    nonce: this.nonce
                },
                data: {
                    action: 'wpplayer',
                    type: this.attr.type,
                    id: this.attr.id,
                    source: this.attr.source
                }
            }).done(function(json) {
                if (json.status && json.data.list && json.data.list.length) {
                    _this.data = json.data.list;
                    _this.createList().getSongInfo().addEvent();
                }
            }).fail(function() {
                window.console && window.console.info('获取歌曲列表失败');
            });
        },

        // 获取歌曲信息
        // 在设置随机播放情况下 flag 参数 控制是否强制指定歌曲播放
        // 为 true 时则播放 val 值的歌曲
        getSongInfo: function(val, flag) {
            var index = typeof val == 'undefined' ? 0 : val,
                _this = this,
                DOM = this.DOM,
                info = {};

            DOM.time.text('00:00');
            DOM.title.text('Loading...');
            DOM.author.text('Loading...');

            if (this.attr.random == 1 && !flag) {
                index = this.random();
            }
            info = this.data[index];
            this.index = index;
            this.setList();

            if (!info || info.url || this.isLoad) {
                return;
            }

            this.isLoad = true;
            $.ajax({
                url: this.url,
                type: 'post',
                headers: {
                    nonce: this.nonce
                },
                data: {
                    action: 'wpplayerGetInfo',
                    id: info.id,
                    pic_id: info.pic_id,
                    url_id: info.url_id,
                    lyric_id: info.lyric_id,
                    source: this.attr.source
                }
            }).done(function(json) {
                _this.isLoad = false;
                if (json.status && json.data) {
                    _this.data[index]['lyric'] = json.data.lyric.lyric;
                    _this.data[index]['pic'] = json.data.pic.url;
                    _this.data[index]['url'] = json.data.url.url;
                    _this.createSound(index);
                }
            }).fail(function() {
                _this.isLoad = false;
                window.console && window.console.info('获取歌曲失败');
            });

            return this;
        },

        // 本地上传操作
        localAction: function() {
            if (typeof this.attr.address === 'undefined') return false;

            function transformArray(str) {
                var len = str.length-1;
                return str.substr(0, len).split('|');
            }
            
            var data = [],
                index = 0,
                title = transformArray(this.attr.title),
                artist = transformArray(this.attr.author),
                location = transformArray(this.attr.address),
                pic = transformArray(this.attr.thumb);
            
            $.each(title, function(i) {
                data.push({
                    name: title[i],
                    lyric: "",
                    artist: [artist[i]],
                    url: location[i],
                    pic: pic[i]
                });
            });

            this.data = data;
            this.createList();

            if (this.attr.random == 1) {
                index = this.random();
            }
            this.index = index;
            this.setList();

            this.createSound(this.index).addEvent();
        },

        // 创建音乐列表
        createList: function() {
            var i = 0,
                tpl = '',
                DOM = this.DOM,
                data = this.data,
                len = data.length;

            for (; i<len; i++) {
                var odd = i % 2 ? 'odd' : '';
                tpl += WPPlayer.template
                        .replace('{i}', i)
                        .replace('{class}', odd)
                        .replace('{author}', data[i].artist.join("/"))
                        .replace('{serial}', i+1)
                        .replace('{title}', data[i].name);
            }

            $(tpl).appendTo(DOM.list.children('ul')).eq(this.index).addClass('current');
            DOM.list.height(DOM.list.outerHeight());

            return this;
        },

        // 创建声音
        createSound: function(val) {
            var _this = this,
                index = (typeof val === 'undefined') ? 0 : val,
                data = this.data[index],
                DOM = this.DOM,
                autoplay = (this.single == 'true' && this.attr.autoplay == "1" && !this.isMobile ) ? true : false;

            //setting DOM
            DOM.title.text(data.name);
            DOM.author.text(data.artist.join("/"));
            DOM.thumb.find('img').attr('src', data.pic);

            if (this.data.length > 1 && !data.url) {
                this.nextSound();
                return;
            }

            soundManager.onready(function() {
                if (typeof _this.sound === 'object') _this.sound.destruct();

                _this.timeReady = _this.isMobile ? true : false;

                //create sound
                _this.sound = soundManager.createSound({
                        url: data.url,
                        onload: function() {
                            _this.timeReady = true;
                        },
                        onplay: function() { _this.setPlay() },
                        onresume: function() { _this.setPlay() },
                        onpause: function() { _this.setStop() },
                        onfinish: function() { _this.nextSound() },
                        whileplaying: function() {
                            var count, minute, second, pre,
                                position = (this.position / this.duration)*100,
                                playbar = position > 100 ? '100%' : position.toFixed(5) + '%';

                            if (_this.timeReady) {
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
                            DOM.time.text(pre + minute +':'+ second);
                            
                            if (_this.attr.lyric && _this.lyric && !_this.isMobile) _this.setLyric(this.position);
                        },
                        whileloading: function() {
                            var seekbar = (this.bytesTotal && this.bytesLoaded != this.bytesTotal) ? (this.bytesLoaded / this.bytesTotal) * 100 : 100;
                            DOM.seekbar.width(seekbar+'%');
                        },
                        onerror: function(code, description) {
                            window.console && console.log(this.id + ' failed?', code, description);
                            if (_this.data.length > 1) {
                                _this.nextSound();
                                return;
                            }
                        }
                    });

                _this.soundEvent();

                if (_this.attr.lyric && !_this.isMobile) {
                    data.lyric ? _this.createLyric(data.lyric) : _this.noLyric();
                }

                if (_this.first && !autoplay) {
                    _this.first = false;
                    return;
                }

                _this.sound.play();

            });

            return this;
        },
        
        //创建歌词
        createLyric: function(lyric) {
        
            //清空歌词
            this.emptyLyric();
            
            var i = 0,
                cache = [],
                list = lyric.split("\n"),
                len = list.length,
                DOM = this.DOM,
                reg = /\[(\d+:\d+.?\d+)\]/g,
                regStr = /[^\[(\d+):(\d+.?\d+)\]\s*].+/g,
                tpl = '';
            
            //匹配歌词
            for (; i < len; i++) {
                var arr = $.trim(list[i]).match(reg);
                if ($.isArray(arr)) {
                    var str = $.trim(list[i]).match(regStr);
                    for (var j = 0; j < arr.length; j++){
                        var t = arr[j].replace('[', '').replace(']', '').split(':'),
                            time = (t[0] * 60) + Math.floor(t[1]);
                        cache.push({time: time, lyric: $.isArray(str) ? str[0] : '&nbsp;' });
                    }
                }
            }

            if (!cache.length) {
                this.noLyric();
                return false;
            }

            //排序
            i = 0;
            len = cache.length;
            for (; i < len; i++) {
                for (var j = i; j < len; j++) {
                    if ( cache[i].time > cache[j].time ) {
                        var temp = cache[i];
                        cache[i] = cache[j];
                        cache[j] = temp;
                    }
                }
            }
            
            //模板
            this.lyric = {};
            for (i = 0; i < len; i++) {
                tpl += '<li>' + cache[i].lyric + '</li>';
                this.lyric[cache[i].time] = i;
            }
            DOM['lyricList'] = $(tpl).appendTo(DOM.lyrics.children('ul'));
            
            var list = DOM.lyrics.children('ul').height(),
                minH = Math.floor(this.lyricH / 2),
                maxH = list - minH;

            this.offset = {
                min: Math.floor(minH / this.line),
                max: Math.floor(maxH / this.line)
            };
        },

        //设置歌词
        setLyric: function(val) {
            var DOM = this.DOM,
                offset = this.offset,
                time = Math.floor(val / 1000),
                index = this.lyric && this.lyric[time],
                scroll = 0;
            
            // setting class
            if (typeof index !== 'undefined' && DOM.lyricList && this.mark != index) {
                
               if (index >= offset.min) scroll = index - offset.min;
               if (index >= offset.max) scroll = offset.max - offset.min;
               
                DOM.lyricList.removeClass('current').eq(index).addClass('current');
                DOM.lyrics.children('ul').css('margin-top', -scroll * this.line);
                
                this.mark = index;
            }
        },

        //清空歌词
        emptyLyric: function() {
            var DOM = this.DOM;
            this.lyric = null;
            DOM.lyrics.children('ul').css('margin-top', 0).html('');
        },
        
        //暂无歌词
        noLyric: function(val) {
            var DOM = this.DOM,
                str = typeof val === 'undefined' ? '\u6682\u65e0\u6b4c\u8bcd' : val;
            this.lyric = null;
            DOM.lyrics.children('ul').html('<li>'+str+'</li>');
        },
        
        //播放器事件
        addEvent: function() {
            var DOM = this.DOM,
                _this = this,
                eventType = this.isMobile ? 'touchend' : 'click',
                startTx, startTy;
            
            //show list
            DOM.listbtn.on(eventType, function() {
                var has = $(this).hasClass('wp-player-open');
                DOM.lyrics.children('ul').stop(true, true).hide();
                $(this)[has ? 'removeClass' : 'addClass']('wp-player-open');
                DOM.list.show()[has ? 'removeClass': 'addClass']('wp-player-list-hide');
            });

            //show lyrics
            if (this.attr.lyric){
                DOM.lyricsbtn.on(eventType, function() {
                    DOM.listbtn.addClass('wp-player-open');
                    DOM.list.hide();
                    DOM.lyrics.children('ul').stop(true, true).fadeIn();
                });
            }
            
            //select song event
            var selectSong = function(val) {
                var $elem = typeof val === 'undefined' ? $(this) : $(val).parents('li'),
                    index = parseInt($elem.attr('data-index'), 10),
                    has = $elem.hasClass('current') && _this.sound.playState > 0;
                (index < 0 || index > _this.data.length-1) ? _this.index = 0 : _this.index = index;
                
                if (has && !_this.sound.paused) {
                    _this.sound.pause();
                } else if (has && _this.sound.paused) {
                    _this.sound.resume()
                } else {
                    _this.sound && _this.sound.pause();
                    _this.reset().setList();
                    if (_this.data[_this.index] && _this.data[_this.index].url) {
                        _this.createSound(_this.index);
                    } else {
                        _this.getSongInfo(_this.index, true);
                    }
                }
            };

            //判断当前是否为移动端
            //如果是移动端采用 tap 替代 click 事件
            if (!this.isMobile) {
                DOM.list.on('click', 'li', function() { selectSong.call(this); });
            } else {
                DOM.list[0].addEventListener('touchstart',  function(e) {
                    var nodeName = e.target.nodeName;
                    if (nodeName === 'A' || nodeName === 'SPAN') {
                        var touches = e.touches[0];
                        startTx = touches.clientX;
                        startTy = touches.clientY;
                    }
                }, false);
                
                DOM.list[0].addEventListener('touchend', function(e) {
                    var nodeName = e.target.nodeName;
                    if (nodeName === 'A' || nodeName === 'SPAN') {
                        var touches = e.changedTouches[0],
                            endTx = touches.clientX,
                            endTy = touches.clientY;
                        
                        if( Math.abs(startTx - endTx) < 6 && Math.abs(startTy - endTy) < 6 ){
                            selectSong(e.target);
                            startTx = null;
                            startTy = null;
                        }
                    }
                }, false);
            }

            return this;
        },

        // SoundManage Event
        soundEvent: function() {
            var DOM = this.DOM,
                _this = this,
                eventType = this.isMobile ? 'touchend' : 'click';
            
            //sound play
            if (!this.isMobile) {
                DOM.seekbar.off().on(eventType, function(event) { _this.seekbar(event) });
            }
            DOM.play.off().on(eventType, function() { _this.play() });
            DOM.stop.off().on(eventType, function() { _this.stop() });

            //prev, next
            if (this.data.length > 1) {
                DOM.previous.off().on(eventType, function() { _this.prevSound() });
                DOM.next.off().on(eventType, function() { _this.nextSound() });
            }

            return this;
        },

        // 播放进度 Event
        seekbar: function(event) {
            var DOM = this.DOM,
                _x = event.offsetX ? event.offsetX : (event.clientX - DOM.progress.offset().left).toFixed(0);
            var offsetX = (_x / DOM.progress.width()) * this.sound.duration;
            if (offsetX < 0) offsetX = 0;
            if (offsetX > this.sound.duration) offsetX = this.sound.duration;
            this.sound.setPosition(offsetX);
            
            DOM.lyricList && DOM.lyricList.removeClass('current');
        },

        //播放 Event
        play: function() {
            this.sound[this.sound.playState < 1 ? 'play' : 'resume']();
        },

        //暂停 Event
        stop: function() {
            this.sound.pause();
        },

        // 上一首 Event
        prevSound: function() {
            var minIndex = 0;

            if (this.attr.random == 1) {
                this.index = this.random();
            } else {
                if (--this.index < minIndex) {
                    this.index = this.data.length - 1;
                }
            }

            this.sound && this.sound.pause();
            this.reset().setList();
            if (this.data[this.index] && this.data[this.index].url) {
                this.createSound(this.index);
            } else {
                this.getSongInfo(this.index, true);
            }
        },

        // 下一首 Event
        nextSound: function() {
            var maxIndex = this.data.length-1;

            if (this.attr.random == 1) {
                this.index = this.random();
            } else {
                if (++this.index > maxIndex) {
                    this.index = 0;
                }
            }

            this.sound && this.sound.pause();
            this.reset().setList();
            if (this.data[this.index] && this.data[this.index].url) {
                this.createSound(this.index);
            } else {
                this.getSongInfo(this.index, true);
            }
        },

        // 设置当前播放状态
        setPlay: function() {
            var DOM = this.DOM;
            DOM.playing.stop(true,true)[(this.IE6 || this.isMobile ) ? 'show' : 'fadeIn']();
            DOM.play.hide();
            DOM.stop.show();
            return this;
        },

        // 设置当前暂停状态
        setStop: function() {
            var DOM = this.DOM;
            DOM.playing.stop(true,true)[(this.IE6 || this.isMobile ) ? 'hide' : 'fadeOut']();
            DOM.play.show();
            DOM.stop.hide();
            return this;
        },

        // 获取随机数
        random: function() {
            if (!this.data || this.data.length === 1) {
                return 0;
            }
            return Math.floor(Math.random() * this.data.length) || 0;
        },

        // 重置播放器界面
        reset: function() {
            var DOM = this.DOM;
            this.setStop();
            DOM.seekbar.width(0);
            DOM.playbar.width(0);
            return this;
        },

        // 设置列表选中
        setList: function() {
            var DOM = this.DOM,
                index = this.index,
                $list = DOM.list.find('li'),
                liHeight = $list.eq(0).outerHeight(),
                number = Math.floor(DOM.list.height() / liHeight),
                mean = Math.floor(number / 2),
                min = mean,
                max = this.data.length - mean;

            if (this.data.length > number) {
                if (index <= min) {
                    DOM.list.scrollTop(0);
                }

                if (index > min && index < max) {
                    DOM.list.scrollTop((index - mean) * liHeight);
                }

                if (index >= max) {
                    DOM.list.scrollTop(DOM.list.children('ul').outerHeight());
                }
            }

            DOM.list.find('li').removeClass('current').eq(this.index).addClass('current');
            return this;
        },

        // 获取播放器DOM
        getDOM: function($elem) {
            var elem = $elem[0].getElementsByTagName('*'),
                DOM = {};

            DOM['wrap'] = $elem;
            for (var i = 0; i < elem.length; i++) {
                if (elem[i].className.indexOf('wp-player') > -1 ) {
                    var name = elem[i].className.replace('wp-player', '').replace(/-/g, '');
                    DOM[name] = $(elem[i]);
                }
            }

            this.DOM = DOM;
            return this;
        },

        // 格式化时间
        formatNumber: function(val) {
            return val.toString().length < 2 ? '0' + val : val;
        },

        // 获取播放器必须的属性
        getAttr: function() {
            var DOM = this.DOM,
                lyric = DOM.wrap.attr('data-lyric'),
                open = typeof lyric === 'undefined' ? false : (lyric == 'open' ? true : false);
            
            this.attr = {
                source: DOM.wrap.attr('data-source'),
                type: DOM.wrap.attr('data-type'),
                id: DOM.wrap.attr('data-id'),
                title: DOM.wrap.attr('data-title'),
                author: DOM.wrap.attr('data-author'),
                address: DOM.wrap.attr('data-address'),
                thumb: DOM.wrap.attr('data-thumb'),
                autoplay: DOM.wrap.attr('data-autoplay'),
                random: DOM.wrap.attr('data-random'),
                lyric: open
            };
            return this;
        },

        /**
         * @name 销毁播放器
         */
        destroy: function() {

            this.sound && this.sound.unload();
            this.sound = "";

            if (this.attr.lyric) {
                this.DOM.lyrics.children('ul').html('');
                this.DOM.listbtn.trigger(this.isMobile ? 'touchend' : 'click').removeClass('wp-player-open');
            }
            this.reset();
            this.DOM.list.removeClass('wp-player-list-hide').children('ul').html('');
            this.DOM.thumb.children('img').attr('src', this.img);
            this.DOM.time.text('00:00');
            this.DOM.title.text('Loading...');
            this.DOM.author.text('Loading...');
            this.DOM.wrap.data('WPPlayer', "");
            this.DOM.wrap.off().find('*').off();

            this.index = 0;
            this.lyric = null;
            this.offset = {};
            this.mark = null;
            this.lyricH = 300;
            this.line = 27;
            this.isLoad = false;
            this.first = true;
            this.attr = {};
            this.data = [];
            this.img = null;
            this.DOM = {};
        },

        /**
         * 重新载入播放器
         */
        reload: function() {
            this.destroy();
            this.getDOM(this.$elem).getAttr().init();
            this.DOM.wrap.data('WPPlayer', this);
        }
    };

    // 列表模板
    WPPlayer.template = '<li data-index="{i}" class="{class}"><a href="javascript:void(0);"><span class="wp-player-list-author">{author}</span><span class="wp-player-list-order">{serial}</span><span class="wp-player-list-title">{title}</span></a></li>';

    // 扩展 jQuery 对象
    $.fn.WPPlayer = function(options) {
        return this.each(function(){
            var $elem = $(this);
            typeof options === 'string'
                ? ($elem.data('WPPlayer') && $elem.data('WPPlayer')[options]())
                : $elem.data('WPPlayer', new WPPlayer(this, options));
        });
    };

    return $;
}(jQuery, soundManager);

jQuery('[data-wp-player="wp-player"]').WPPlayer( wp_player_params );