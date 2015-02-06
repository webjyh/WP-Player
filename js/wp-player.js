/**
 * @name     wp-player
 * @desc     初始化播放器。
 * @depend   jQuery, SoundManager2
 * @author   M.J
 * @date     2014-12-21
 * @update   2015-02-06
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * @reutn    {jQuery}
 * @version  2.5.1
 * 
 */
~function($, soundManager) {

    var WPPlayer = function(elem, options) {

        soundManager.setup({ url: options.swf, debugMode: false });

        this.index = 0;                                     //当前播放歌曲
        this.url = options.url;                             //Ajax请求地址
        this.nonce = options.nonce;                         //WordPress 随机数
        this.IE6 = !-[1,] && !window.XMLHttpRequest;        //是否是IE 6
        this.single = options.single;                       //是否是详情页
        this.lyric = null;                                  //当前歌曲歌词
        this.offset = {};                                   //歌词滚动最小，最大值
        this.mark = null;                                   //歌词滚动标记
        this.lyricH = 300;                                  //歌词容器高度
        this.line = 27;                                     //歌词单行行高
        this.isMobile = 'createTouch' in document && !('onmousemove' in document) || /(iPhone|iPad|iPod)/i.test(navigator.userAgent);

        this.getDOM($(elem)).getAttr().init();
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
            
            //各类型操作
            if (!attr.xiami) {
                this.localAction();
            } else {
                (attr.source == 'xiami') ? this.xiamiAction() :  this.neteaseAction();
            }
        },
        
        // 网易云音乐操作
        neteaseAction: function() {
            var type = this.attr.type,
                id = this.attr.xiami,
                _this = this;
            $.ajax({ url: this.url, type: 'get', headers: { nonce: this.nonce }, data: { action: 'wpplayer', type: type, id: id } })
             .done(function(json) {
                if (json.status && json.data.trackList){
                    _this.data = json.data.trackList;
                    _this.createList().createSound().addEvent();
                }
             });
        },

        // 虾米类型操作
        xiamiAction: function() {
            var type = this.getXiamiType(this.attr.type),
                xiami = this.attr.xiami,
                _this = this,
                url = 'http://www.xiami.com/song/playlist/id/'+xiami+'/type/'+type+'/cat/json?callback=?';

            $.getJSON(url, function(json) {
                if (json.status && json.data.trackList) {
                    for (var i=0, length = json.data.trackList.length; i<length; i++) {
                        json.data.trackList[i].location = _this.getXiamiLocation( json.data.trackList[i].location );
                    }
                    _this.data = json.data.trackList;
                    _this.createList().createSound().addEvent();
                } else {
                    _this.getSinaApi();
                }
            }).fail(function() {
                _this.getSinaApi();
            });
        },

        // 如果抓取失败，采用新浪云
        getSinaApi: function() {
            var type = typeof this.attr.type == 'undefined' ? 'song' : this.attr.type,
                xiami = this.attr.xiami,
                _this = this;
            
            $.getJSON('http://wpplayer.sinaapp.com/?callback=?', { act: type, id: xiami }, function(data) {
                if (data.code > 0 && data.data.length) {
                    _this.data = data.data;
                    _this.createList().createSound().addEvent();
                }
            });
        },

        // 本地上传操作
        localAction: function() {
            if (typeof this.attr.address === 'undefined') return false;

            function transformArray(str) {
                var len = str.length-1;
                return str.substr(0, len).split('|');
            }
            
            var data = [],
                title = transformArray(this.attr.title),
                artist = transformArray(this.attr.author),
                location = transformArray(this.attr.address),
                pic = transformArray(this.attr.thumb);
            
            $.each(title, function(i) {
                data.push({
                    title: title[i],
                    artist: artist[i],
                    location: location[i],
                    pic: pic[i]
                });
            });

            this.data = data;
            this.createList().createSound().addEvent();
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
                        .replace('{author}', data[i].artist)
                        .replace('{serial}', i+1)
                        .replace('{title}', data[i].title);
            }

            $(tpl).appendTo(DOM.list.children('ul')).first().addClass('current');

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
            DOM.title.text(data.title);
            DOM.author.text(data.artist);
            DOM.thumb.find('img').attr('src', data.pic);

            soundManager.onready(function() {
                if (typeof _this.sound === 'object') _this.sound.destruct();

                _this.timeReady = _this.isMobile ? true : false;

                //create sound
                _this.sound = soundManager.createSound({
                        url: data.location,
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
                        }
                    });

                _this.soundEvent();

                if (_this.attr.lyric && !_this.isMobile) _this.getLyric();

                if (typeof val !== 'undefined' || autoplay) _this.sound.play();

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

        //获取歌词
        getLyric: function() {
            
            //容错处理
            this.noLyric(this.attr.xiami ? '\u6b63\u5728\u52a0\u8f7d\u6b4c\u8bcd...' : '\u6682\u65e0\u6b4c\u8bcd');
            if (!this.attr.xiami) return false;

            var _this = this,
                url = this.url + '?action=wpplayerGetLrc&type=' + this.attr.source + '&id=' + this.data[this.index].song_id,
                lyric = this.attr.source == 'xiami' ? this.data[this.index].lyric_url : '';

            $.ajax({ url: url, type: 'post', headers: { nonce: this.nonce }, data: { lyric: lyric } })
             .done(function(data) { 
                (data.status && data.lyric) ? _this.createLyric(data.lyric) : _this.noLyric();
             }).fail(function() { 
                _this.noLyric(); 
             });

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
                    _this.reset().setList().createSound(_this.index);
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
            if (--this.index < minIndex) this.index = this.data.length-1;
            this.reset().setList().createSound(this.index);
        },

        // 下一首 Event
        nextSound: function() {
            var maxIndex = this.data.length-1;
            if (++this.index > maxIndex) this.index = 0;
            this.reset().setList().createSound(this.index);
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
            var DOM = this.DOM;
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
                xiami: DOM.wrap.attr('data-xiami'),
                title: DOM.wrap.attr('data-title'),
                author: DOM.wrap.attr('data-author'),
                address: DOM.wrap.attr('data-address'),
                thumb: DOM.wrap.attr('data-thumb'),
                autoplay: DOM.wrap.attr('data-autoplay'),
                lyric: open
            };
            return this;
        },

        // 虾米类型转换
        getXiamiType: function(val) {
            var type;
            switch (val) {
                case 'song': type = 0; break;
                case 'album': type = 1; break;
                case 'artist': type = 2; break;
                case 'collect': type = 3; break;
                default: type = 0;
            }
            return type;
        },
        
        // 虾米地址转换
        // 参照 http://www.blackglory.me/xiami-getlocation-implementation-of-php-and-javascript/
        getXiamiLocation: function(str) {
            try {
                var a1 = parseInt(str.charAt(0)),
                    a2 = str.substring(1),
                    a3 = Math.floor(a2.length / a1),
                    a4 = a2.length % a1,
                    a5 = [],
                    a6 = 0,
                    a7 = '',
                    a8 = '';
                for (; a6 < a4; ++a6) {
                    a5[a6] = a2.substr((a3 + 1) * a6, (a3 + 1));
                }
                for (; a6 < a1; ++a6) {
                    a5[a6] = a2.substr(a3 * (a6 - a4) + (a3 + 1) * a4, a3);
                }
                for (var i = 0,a5_0_length = a5[0].length; i < a5_0_length; ++i) {
                    for (var j = 0,a5_length = a5.length; j < a5_length; ++j) {
                        a7 += a5[j].charAt(i);
                    }
                }
                a7 = decodeURIComponent(a7);
                for (var i = 0,a7_length = a7.length; i < a7_length; ++i) {
                    a8 += a7.charAt(i) === '^' ? '0': a7.charAt(i);
                }
                return a8;
            } catch(e) {
                return false;
            }
        }
    };

    // 列表模板
    WPPlayer.template = '<li data-index="{i}" class="{class}"><a href="javascript:void(0);"><span class="wp-player-list-author">{author}</span><span class="wp-player-list-order">{serial}</span><span class="wp-player-list-title">{title}</span></a></li>';

    // 扩展 jQuery 对象
    $.fn.WPPlayer = function(options) {
        return this.each(function(){
            new WPPlayer(this, options);
        });
    };

    return $;
}(jQuery, soundManager);

jQuery('[data-wp-player="wp-player"]').WPPlayer( wp_player_params );