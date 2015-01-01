<?php
/**
 * WordPress eXtended WP-Player
 */
if ( !class_exists( 'wp_player_plugin' ) ){

    $WP_PLAYER_VERSION = '2.2.0';

    class wp_player_plugin {

        function __construct(){

            //Include MetaBox
            require_once('metaboxes.php');
            
            add_action( "admin_menu", array( $this, 'options_menu' ) );
            add_filter( 'plugin_action_links', array( $this, 'wp_player_add_link' ), 10, 4 );
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_player_scripts') );
            add_action( 'admin_print_styles', array( $this, 'wp_player_admin_css' ) );
            add_action( 'admin_print_scripts', array( $this, 'wp_player_admin_head' ) );
            add_action( 'wp_ajax_nopriv_wpplayer', array( $this, 'wp_player_netease' ) );
            add_action( 'wp_ajax_wpplayer', array( $this, 'wp_player_netease' ) );
            add_shortcode( 'player', array( $this, 'wp_player_shortcode' ) );

            $this->options = get_option( 'wp_player_options' );
            $this->base_dir = WP_PLUGIN_URL.'/'. dirname( plugin_basename( dirname( __FILE__ ) ) ).'/';
            $this->admin_dir = site_url( '/wp-admin/options-general.php?page=player.php' );
            
        }

        //Register Menu
        function options_menu(){
            add_options_page( 'WP-Player 设置', 'WP-Player 设置', 'manage_options', basename( __FILE__ ), array( $this, 'printAdminPage' ) );
            add_action( 'admin_init', array( $this, 'wp_player_settings' ));
        }

        //Register WP-Player Setting
        function wp_player_settings() {
            register_setting( 'wp_player_settings_group', 'wp_player_options' );
        }

        //Register setting submit
        function wp_player_add_link( $action_links, $plugin_file, $plugin_data, $context ){
            if (strip_tags($plugin_data['Title']) == 'WP-Player') {
                $wp_player_links = '<a href="'.$this->admin_dir.'" title="设定 WP-Player">设定</a>';
                array_unshift( $action_links, $wp_player_links );
            }
            return $action_links;
        }

        //Include scripts
        function wp_player_scripts(){
            global $WP_PLAYER_VERSION;
            $options = $this->options;
            wp_enqueue_style( 'wp-player', $this->base_dir . 'css/wp-player.css', array(), $WP_PLAYER_VERSION, 'screen' );
            if( is_array( $options ) && $options['jQuery'] == 'true' ){
                wp_enqueue_script( 'jquery' );
            }
            wp_enqueue_script( 'wp-player-jplayer', $this->base_dir . 'js/soundmanager2.js', array(), $WP_PLAYER_VERSION, true );
            wp_enqueue_script( 'wp-player', $this->base_dir . 'js/wp-player.js', array(), $WP_PLAYER_VERSION, true );

            wp_localize_script( 'wp-player', 'wp_player_params', 
                array(
                    'swf' => $this->base_dir.'js/',
                    'url' => admin_url().'admin-ajax.php',
                    'nonce' => wp_create_nonce('wp-player'),
                    'single' => ( is_single() || is_page() ) ? 'true' : 'false'
            ));
        }

        //add Admin WP-Player CSS
        function wp_player_admin_css(){
            global $WP_PLAYER_VERSION;
            wp_enqueue_style( 'wp-player-plugin', plugins_url( 'css/wp-player-plugin.css', dirname( __FILE__ ) ), array(), $WP_PLAYER_VERSION );
        }

        //add Admin Upload script
        function wp_player_admin_head(){
            global $WP_PLAYER_VERSION;
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_script( 'wp-plugin-uploader', plugins_url( 'js/plugin-uploader.js', dirname( __FILE__ ) ), array('jquery','media-upload','thickbox'), $WP_PLAYER_VERSION );
        }
        
        /** @name Get Netease Song
         *
         *  @Author: Mufeng 
         *  @URL: http://mufeng.me/hermit-for-wordpress.html
         *
         *  @Author: yanunon
         *  @URL: https://github.com/yanunon/NeteaseCloudMusic/wiki/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90API%E5%88%86%E6%9E%90
        **/
        function wp_player_netease(){
            $type = $_GET['type'];
            $id = intval($_GET['id']);
            $nonce = $_SERVER['HTTP_NONCE'];
            
            if ( !wp_verify_nonce($nonce, "wp-player") || !function_exists('curl_init') ) {
                $JSON = array('status' =>  false, 'message' => '非法请求');
            } else {
                switch ( $type ) {
                    case 'song': $url = "http://music.163.com/api/song/detail/?ids=[$id]"; $key = 'songs'; break;
                    case 'album': $url = "http://music.163.com/api/album/$id?id=$id"; $key = 'album'; break;
                    case 'artist': $url = "http://music.163.com/api/artist/$id?id=$id"; $key = 'artist'; break;
                    case 'collect': $url = "http://music.163.com/api/playlist/detail?id=$id"; $key = 'result'; break;
                    default: $url = "http://music.163.com/api/song/detail/?ids=[$id]"; $key = 'songs';
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Cookie: appver=2.0.2' ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($ch, CURLOPT_REFERER, 'http://music.163.com/;');
                $cexecute = curl_exec($ch);
                curl_close($ch);
                
                if ( $cexecute ) {
                    $result = json_decode($cexecute, true);
                    if ( $result['code'] == 200 && $result[$key] ){
                        $JSON['status'] = true;
                        $JSON['message'] = "";
                        
                        switch ( $key ){
                            case 'songs' : $data = $result[$key]; break;
                            case 'album' : $data = $result[$key]['songs']; break;
                            case 'artist' : $data = $result['hotSongs']; break;
                            case 'result' : $data = $result[$key]['tracks']; break;
                            default : $data = $result[$key]; break;
                        }
                        
                        foreach ( $data as $keys => $data ){
                            $JSON['data']['trackList'][] = array(
                                'title' => $data['name'],
                                'album_name' => $data['album']['name'],
                                'artist' => $data['artists'][0]['name'],
                                'location' => $data['mp3Url'],
                                'pic' => $data['album']['blurPicUrl'].'?param=90x90'
                            );
                        }

                    }
                } else {
                    $JSON = array('status' =>  false, 'message' =>  '非法请求');
                }

            }

            header('Content-type: application/json');
            echo json_encode($JSON);
            die();
        }

        //add shortcode
        function wp_player_shortcode( $atts ){
            global $post;

            extract( shortcode_atts( array( 'autoplay' => 0 ), $atts ) );
            
            $source = get_post_meta( $post->ID, 'wp_player_music_type', true );
            $type = get_post_meta( $post->ID, 'mp3_xiami_type', true );
            $xiami = get_post_meta( $post->ID, 'mp3_xiami', true );
            $title = get_post_meta( $post->ID, 'mp3_title', true );
            $author = get_post_meta( $post->ID, 'mp3_author', true );
            $file = get_post_meta( $post->ID, 'mp3_address', true );
            $thumb = get_post_meta( $post->ID, 'mp3_thumb', true );
            $options = $this->options;
            
            if ( empty( $thumb ) ) $thumb = $this->base_dir.'images/default.png';
            if ( empty( $source ) ) $source = 'xiami';
            
            return '<!--wp-player start--><div class="wp-player" data-wp-player="wp-player" data-source="'.$source.'" data-autoplay="'.$autoplay.'" data-type="'.$type.'" data-xiami="'.$xiami.'" data-title="'.$title.'" data-author="'.$author.'" data-address="'.$file.'" data-thumb="'.$thumb.'"><div class="wp-player-box"><div class="wp-player-thumb"><img src="'.$thumb.'" width="90" height="90" alt="" /><div class="wp-player-playing"><span></span></div></div><div class="wp-player-panel"><div class="wp-player-title"></div><div class="wp-player-author"></div><div class="wp-player-progress"><div class="wp-player-seek-bar"><div class="wp-player-play-bar"><span class="wp-player-play-current"></span></div></div></div><div class="wp-player-controls-holder"><div class="wp-player-time"></div><div class="wp-player-controls"><a href="javascript:;" class="wp-player-previous" title="上一首"></a><a href="javascript:;" class="wp-player-play" title="播放"></a><a href="javascript:;" class="wp-player-stop" title="暂停"></a><a href="javascript:;" class="wp-player-next" title="下一首"></a></div><div class="wp-player-list-btn" title="歌单"></div></div></div></div><div class="wp-player-list"><ul></ul></div></div><!--wp-player end-->';
        }

        //WP-Player Admin Option Page
        function printAdminPage(){ 
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
                                        <li><code>[player autoplay="0"]</code></li>
                                    </ol>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="blogname">使用方法</label></th>
                                <td>					
                                    <b>提供了MetaBox来填写参数</b><br />
                                    <ol>
                                        <li>在虾米网或网易云音乐打开喜欢的歌曲页面，复制歌曲页面的网址如：<code>http://www.xiami.com/song/2078022......</code></li>
                                        <li>并将复制的网址填写到后面的表单内。音乐类型将根据网址自动做出选择。</li>
                                        <li>点击<code>获取音乐ID</code>按钮，此时音乐ID出现在表单中。</li>
                                        <li>将短代码 <code>[player autoplay="1"]</code> 填入您的文章内容中。</li>
                                        <li>短代码中 <code>autoplay</code> 表示是否自动播放；参数<code>"0"</code>表示否；<code>"1"</code>表示是；</li>
                                        <li>支持播放歌单：单音乐页面、专辑页面、艺人页面、精选集页面。</li>
                                        <li><code>PS：</code>建议使用网址来获取音乐ID。</li>
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