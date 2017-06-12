<?php
/**
 * WordPress eXtended WP-Player
 */
if ( !class_exists( 'wp_player_plugin' ) ){
    $WP_PLAYER_VERSION = '2.6.1';
    class wp_player_plugin {

        function __construct() {

            // include Metaboxes
            require_once('metaboxes.php');
            if( !class_exists('Meting') ) {
                require_once('Meting.php');
            }

            add_action( "admin_menu", array( $this, 'options_menu' ) );
            add_filter( 'plugin_action_links', array( $this, 'wp_player_add_link' ), 10, 4 );
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_player_scripts') );
            add_action( 'admin_print_styles', array( $this, 'wp_player_admin_css' ) );
            add_action( 'admin_print_scripts', array( $this, 'wp_player_admin_head' ) );

            add_action( 'wp_ajax_nopriv_wpplayer', array( $this, 'wp_player_actions' ) );
            add_action( 'wp_ajax_wpplayer', array( $this, 'wp_player_actions' ) );

            add_action( 'wp_ajax_nopriv_wpplayerGetInfo', array( $this, 'wp_player_get_info' ) );
            add_action( 'wp_ajax_wpplayerGetInfo', array( $this, 'wp_player_get_info' ) );

            add_shortcode( 'player', array( $this, 'wp_player_shortcode' ) );

            $this->options = get_option( 'wp_player_options' );
            $this->base_dir = WP_PLUGIN_URL.'/'. dirname( plugin_basename( dirname( __FILE__ ) ) ).'/';
            $this->admin_dir = site_url( '/wp-admin/options-general.php?page=player.php' );

            $this->netease = new Meting('netease');
            $this->xiami = new Meting('xiami');
            $this->tencent = new Meting('tencent');
            $this->baidu = new Meting('baidu');

            $this->netease->format(true);
            $this->xiami->format(true);
            $this->tencent->format(true);
            $this->baidu->format(true);
        }

        /**
         * @desc  get actions
         * @param string $source
         * @return Meting
         */
        private function get_api($source = 'netease') {
            switch ( $source ) {
                case 'netease': $API = $this->netease; break;
                case 'xiami': $API = $this->xiami; break;
                case 'tencent': $API = $this->tencent; break;
                case 'baidu': $API = $this->baidu; break;
                default: $this->netease;
            }
            return $API;
        }

        /** @desc Get Netease Song
         *
         *  @Author: METO
         *  @GitHub: https://github.com/metowolf
         *
         *  @name: Meting
         *  @URL: https://github.com/metowolf/Meting
         **/
        public function wp_player_actions() {
            $id = $_POST['id'];
            $type = $_POST['type'];
            $nonce = $_SERVER['HTTP_NONCE'];
            $source = $_POST['source'];

            if ( !wp_verify_nonce($nonce, "wp-player") || !function_exists('curl_init')) {
                $JSON = array('status' =>  false, 'message' => '非法请求', data => array());
            } else {
                $API = $this->get_api($source);
                switch ( $type ) {
                    case 'song': $data = $API->song($id); break;
                    case 'album': $data = $API->album($id); break;
                    case 'artist': $data = $API->artist($id); break;
                    case 'collect': $data = $API->playlist($id); break;
                    default: $data = $API->song($id);
                }
                $JSON = array(
                    'status' =>  ($data && count($data) > 0) ? true : false,
                    'message' =>  ($data && count($data) > 0) ? '获取成功' : '获取失败',
                    'data' => array(
                        'list' => ($data && count($data) > 0) ? json_decode($data) : array()
                    )
                );
            }

            header('Content-type: application/json');
            echo json_encode($JSON);
            die();
        }

        /**
         * @name Get Song Info
         */
        public function wp_player_get_info() {
            $id = $_POST['id'];
            $pic_id = $_POST['pic_id'];
            $url_id = $_POST['url_id'];
            $lyric_id = $_POST['lyric_id'];
            $nonce = $_SERVER['HTTP_NONCE'];
            $source = $_POST['source'];

            if ( !wp_verify_nonce($nonce, "wp-player") || !function_exists('curl_init')) {
                $JSON = array('status' =>  false, 'message' => '非法请求', data => array());
            } else {
                $API = $this->get_api($source);
                $JSON = array(
                    'status' =>  true,
                    'message' =>  '获取成功',
                    'data' => array(
                        'id' => $id,
                        'pic' => json_decode($API->pic($pic_id, 180)),
                        'url' => json_decode($API->url($url_id, 120)),
                        'lyric' => json_decode($API->lyric($lyric_id))
                    )
                );
            }

            header('Content-type: application/json');
            echo json_encode($JSON);
            die();
        }

        /**
         * @desc Register Menu
         */
        public function options_menu(){
            add_options_page( 'WP-Player 设置', 'WP-Player 设置', 'manage_options', basename( __FILE__ ), array( $this, 'printAdminPage' ) );
            add_action( 'admin_init', array( $this, 'wp_player_settings' ));
        }

        /**
         * @desc Register WP-Player Setting
         */
        public function wp_player_settings() {
            register_setting( 'wp_player_settings_group', 'wp_player_options' );
        }

        /**
         * @desc Register setting submit
         */
        public function wp_player_add_link( $action_links, $plugin_file, $plugin_data, $context ){
            if (strip_tags($plugin_data['Title']) == 'WP-Player') {
                $wp_player_links = '<a href="'.$this->admin_dir.'" title="设定 WP-Player">设定</a>';
                array_unshift( $action_links, $wp_player_links );
            }
            return $action_links;
        }

        /**
         * @desc Include scripts
         */
        public function wp_player_scripts(){
            global $WP_PLAYER_VERSION;
            $options = $this->options;
            wp_enqueue_style( 'wp-player', $this->base_dir . 'assets/css/wp-player.css', array(), $WP_PLAYER_VERSION, 'screen' );
            if( is_array( $options ) && $options['jQuery'] == 'true' ){
                wp_enqueue_script( 'jquery' );
            }
            wp_enqueue_script( 'wp-player-jplayer', $this->base_dir . 'assets/js/libs/soundmanager/soundmanager2.js', array(), $WP_PLAYER_VERSION, true );
            wp_enqueue_script( 'wp-player', $this->base_dir . 'assets/js/wp-player.js', array(), $WP_PLAYER_VERSION, true );

            wp_localize_script( 'wp-player', 'wp_player_params', 
                array(
                    'swf' => $this->base_dir.'assets/js/libs/soundmanager/',
                    'img' => $this->base_dir.'assets/images/default.png',
                    'url' => admin_url().'admin-ajax.php',
                    'nonce' => wp_create_nonce('wp-player'),
                    'single' => ( is_single() || is_page() ) ? 'true' : 'false'
            ));
        }

        /**
         * @desc add Admin WP-Player CSS
         */
        public function wp_player_admin_css(){
            global $WP_PLAYER_VERSION;
            wp_enqueue_style( 'wp-player-plugin', plugins_url( 'assets/css/wp-player-plugin.css', dirname( __FILE__ ) ), array(), $WP_PLAYER_VERSION );
        }

        /**
         * @desc add Admin Upload script
         */
        public function wp_player_admin_head(){
            global $WP_PLAYER_VERSION;
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_script( 'wp-plugin-uploader', plugins_url( 'assets/js/plugin-uploader.js', dirname( __FILE__ ) ), array('jquery','media-upload','thickbox'), $WP_PLAYER_VERSION );
        }

        /**
         * @desc string replace
         */
        private function each($str, $isThumb = false) {
            $arr = explode("\r", $str);
            $text = '';
            if (is_array($arr)) {
                foreach ( $arr as $val ) {
                    $val = trim($val);
                    if ( !empty($val) ) {
                        $text .= $val.'|';
                    } elseif ($isThumb) {
                        $text .= $this->base_dir.'images/default.png'.'|';
                   }
                }
            }
            return $text;
        }
        
        /**
         * @desc get MetaBox
         */
        private function get_source() {
            global $post;
            
            $result = array();
            $source = get_post_meta( $post->ID, 'wp_player_music_type', true );
            
            $result['source'] =  empty($source) ? 'xiami' : $source;
            $result['xiami'] = get_post_meta( $post->ID, 'mp3_xiami', true );
            $result['title'] = $this->each(trim(get_post_meta( $post->ID, 'mp3_title', true )));
            $result['author'] = $this->each(trim(get_post_meta( $post->ID, 'mp3_author', true )));
            $result['file'] = $this->each(trim(get_post_meta( $post->ID, 'mp3_address', true )));
            $result['thumb'] = $this->each(get_post_meta( $post->ID, 'mp3_thumb', true ), true);
            $result['type'] = get_post_meta( $post->ID, 'mp3_xiami_type', true );
            
            $lyric = get_post_meta( $post->ID, 'wp_player_lyric_open', true );
            if ( !empty( $lyric ) && $lyric == 'open' ) {
                $result['open'] = $lyric;
                $result['output'] = '<div class="wp-player-lyrics-btn" title="歌词"></div>';
            } else {
                $result['open'] = 'close';
                $result['output'] = '';
            }

            return $result;
        }

        /**
         * @desc add shortcode
         */
        public function wp_player_shortcode( $atts ){
            global $post;

            extract( shortcode_atts( array(
                'autoplay' => 0,
                'random' => 0
            ), $atts ) );

            $data = $this->get_source();
            $img = $this->base_dir.'assets/images/default.png';

            return '<!--wp-player start--><div class="wp-player" data-wp-player="wp-player" data-source="'.$data['source'].'" data-autoplay="'.$autoplay.'" data-random="'.$random.'" data-type="'.$data['type'].'" data-id="'.$data['xiami'].'" data-title="'.$data['title'].'" data-author="'.$data['author'].'" data-address="'.$data['file'].'" data-thumb="'.$data['thumb'].'" data-lyric="'.$data['open'].'"><div class="wp-player-box"><div class="wp-player-thumb"><img src="'.$img.'" width="90" height="90" alt="" /><div class="wp-player-playing"><span></span></div></div><div class="wp-player-panel"><div class="wp-player-title"></div><div class="wp-player-author"></div><div class="wp-player-progress"><div class="wp-player-seek-bar"><div class="wp-player-play-bar"><span class="wp-player-play-current"></span></div></div></div><div class="wp-player-controls-holder"><div class="wp-player-time"></div><div class="wp-player-controls"><a href="javascript:;" class="wp-player-previous" title="上一首"></a><a href="javascript:;" class="wp-player-play" title="播放"></a><a href="javascript:;" class="wp-player-stop" title="暂停"></a><a href="javascript:;" class="wp-player-next" title="下一首"></a></div>'.$data['output'].'<div class="wp-player-list-btn" title="歌单"></div></div></div></div><div class="wp-player-main"><div class="wp-player-list"><ul></ul></div><div class="wp-player-lyrics"><ul></ul></div></div></div><!--wp-player end-->';
        }

        /**
         * @desc WP-Player Admin Option Page
         */
        public function printAdminPage(){
            ?>
            <div class="wrap">
                <div id="icon-options-general" class="icon32"><br></div><h2>WP-Player 设置</h2><br>
                <form method="post" action="options.php">
                    <?php settings_fields( 'wp_player_settings_group' ); ?>
                    <?php $options = $this->options; ?>
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="blogname">短代码</label></th>
                                <td>
                                    <b>文章中插入短代码：</b><br />
                                    <ol>
                                        <li><code>[player]</code></li>
                                        <li><code>[player autoplay="0" randplay="1"]</code></li>
                                    </ol>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="blogname">使用方法</label></th>
                                <td>					
                                    <b>提供了MetaBox来填写参数</b><br />
                                    <ol>
                                        <li>WP-Player 支持网易云音乐, 虾米音乐, QQ音乐, 百度音乐平台</li>
                                        <li>如在网易云音乐打开喜欢的歌曲页面，复制歌曲页面的网址如：<code>http://music.163.com/#/song?id=191213</code></li>
                                        <li>并将复制的网址填写到后面的表单内。音乐类型将根据网址自动做出选择。</li>
                                        <li>点击<code>获取音乐ID</code>按钮，此时音乐ID出现在表单中。</li>
                                        <li>将短代码 <code>[player autoplay="1" random="1"]</code> 填入您的文章内容中。</li>
                                        <li>短代码中 <code>autoplay</code> 表示是否自动播放；参数<code>"0"</code>表示否；<code>"1"</code>表示是；</li>
                                        <li>短代码中 <code>random</code> 表示是否随机播放；参数<code>"0"</code>表示否；<code>"1"</code>表示是；</li>
                                        <li>支持播放歌单：单音乐页面、专辑页面、艺人页面、精选集页面。</li>
                                        <li><code>PS：</code>本插件需要您的服务器或主机支持 PHP 5.4+ and Curl, OpenSSL 模块已安装。</li>
                                        <li><code>Tips：</code>本插件仅供个人学习研究使用，请勿作为各种商业用户，音乐版权归各音乐平台所有。</li>
                                    </ol>
                                </td>
                            </tr>					
                            <tr valign="top">
                                <th scope="row"><label for="blogname">载入自带 jQuery 文件</label></th>
                                <td>
                                    <fieldset>
                                        <label for="wp_player_options[jQuery]">
                                            <input type="checkbox" id="wp_player_options[jQuery]" name="wp_player_options[jQuery]" value="true" <?php if ( is_array( $options ) && $options['jQuery'] == 'true' ){ echo 'checked="checked"'; } ?> />
                                            点击选中 jQuery (<small>有些主题以自带jQuery库，如已有则取消此选项</small>)
                                        </label>
                                    </fieldset>						
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="wp_player_submit_form">
                        <br />
                        <input type="submit" class="button-primary wp_player_submit_form_btn" name="save" value="<?php _e('Save Changes') ?>"/>
                    </div>
                </form>
            </div>
<?php
        }
    }
}
?>