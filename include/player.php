<?php
/**
 * WordPress eXtended WP-Player
 */
if ( !class_exists( 'wp_player_plugin' ) ){

	class wp_player_plugin {
	
		function __construct(){

			//Include MetaBox
			require_once('metaboxes.php');
			
			add_action( "admin_menu", array( $this, 'options_menu' ) );
			add_filter( 'plugin_action_links', array( $this, 'wp_player_add_link' ), 10, 4 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_player_scripts') );
			add_action( 'wp_footer', array( $this, 'wp_player_scripts_init' ) );
			add_action( "admin_print_scripts", array( $this, 'wp_player_admin_head' ) );
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
			$options = get_option( 'wp_player_options' );
			if ( !is_array( $options ) || empty( $options['api'] ) || $options['api'] == 'wpplayer.duapp.com' ){
				$defaults = array( 'jQuery'=>'true', 'themes'=>2, 'api'=>'wpplayer.sinaapp.com' );
				delete_option('wp_player_options');
				add_option( 'wp_player_options', $defaults );
			}
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
			$options = $this->options;
			wp_enqueue_style( 'wp-player', $this->base_dir . 'images/wp-player.min.css', array(), '1.3.2', 'screen' );
			if( $options['jQuery'] == 'true' ){
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_script( 'wp-player-jplayer', $this->base_dir . 'js/jquery.jplayer.min.js', array(), '1.3.2', true );
			wp_enqueue_script( 'wp-player', $this->base_dir . 'js/wp-player.js', array(), '1.3.2', true );
			
		}
		
		//script init
		function wp_player_scripts_init(){
			$options = $this->options;
			
			if ( is_single() || is_page() ){
				$is_is_single = 'true';
			} else {
				$is_is_single = 'false';
			}
			echo '<script type="text/javascript">var wp_player_params = { domain : "'.plugins_url( 'images', dirname( __FILE__ ) ).'", is_single : '.$is_is_single.', api : "'.$options['api'].'" };</script>'."\r\n";

		}

		//add Admin Upload script
		function wp_player_admin_head(){
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_script( 'plugin-uploader', plugins_url( 'js/plugin-uploader.js', dirname( __FILE__ ) ), array('jquery','media-upload','thickbox'), '1.3.2' );
		}
		
		//add shortcode
		function wp_player_shortcode( $atts ){
			global $post;

			extract( shortcode_atts( array( 'loop' => 1, 'autoplay' => 0 ), $atts ) );
			
			$type = get_post_meta( $post->ID, 'wp_player_type', true );
			$title = get_post_meta( $post->ID, 'mp3_title', true );
			$author = get_post_meta( $post->ID, 'mp3_author', true );
			$xiami = get_post_meta( $post->ID, 'mp3_xiami', true );
			$file = get_post_meta( $post->ID, 'mp3_address', true );
			$thumb = get_post_meta( $post->ID, 'mp3_thumb', true );
			$options = $this->options;
			
			if ( $type == 0 ){
				$title = 'Loading...';
				$author = 'Loading...';
			}
			
			if ( empty( $thumb ) ){
				$thumb = $this->base_dir.'images/wp_player_thumb.jpg';
			}
			
			if ( $options['themes'] == 2 ){
				$class = " wp-player-themes";
				$thumb = $this->base_dir.'images/wp_player_thumb_2.jpg';
			}
			
			return '<div class="wp-player-audio'.$class.'"><div class="entry-music" data-audio-id="'.$post->ID.'" data-audio-xiami="'.$xiami.'" data-audio-file="'.$file.'" data-audio-loop="'.$loop.'" data-audio-autoPlay="'.$autoplay.'" data-audio-themes="'.$options['themes'].'"><div id="jp_jplayer_'.$post->ID.'" class="jp-jplayer"></div><div id="jp_container_'.$post->ID.'" class="music-box jp-audio"><div class="music-author"><img src="'.$thumb.'" width="38" height="38" /></div><div class="music-text"><h3>'.$title.'</h3><p>'.$author.'</p></div><div class="jp-type-single"><div class="music-controls"><ul class="clearfix jp-controls"><li><a href="javascript:;" class="stop jp-stop"></a></li><li><a href="javascript:;" class="play jp-play"></a></li><li><a href="javascript:;" class="pause jp-pause"></a></li></ul></div><div class="music-progress jp-progress"><div class="music-seek-bar jp-seek-bar"><div class="music-play-bar jp-play-bar"><span class="play-current"></span></div></div></div></div></div></div></div>';
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
								<th scope="row"><label for="blogname">使用方法</label></th>
								<td>
									<b>文章中插入短代码：</b><br />
									<ol>
										<li><code>[player]</code></li>
										<li><code>[player loop="0" autoplay="0"]</code></li>
									</ol>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="blogname">参数选择</label></th>
								<td>					
									<b>提供了MetaBox来填写参数</b><br />
									<ol>
										<li>填写虾米歌曲ID 如有不知道如何获取 点击<a href="http://webjyh.com/wp-player/" target="_blank">这里</a></li>
										<li>自定义上传mp3文件</li>
										<li>自定义上传歌曲缩略图</li>
										<li>自定义歌曲名，歌手名</li>
										<li>自定义循环播放 <code>loop="1"</code> 和 自动播放 <code>autoplay="1"</code></li>
										<li>在同时填写虾米ID和歌曲地址时，优先采用虾米</li>
										<li>自动播放只有在具体文章页面和Page页面才有用</li>
										<li>PS：一篇文章只能插入一个播放器</li>
									</ol>
								</td>
							</tr>					
							<tr valign="top">
								<th scope="row"><label for="blogname">载入自带 jQuery 文件</label></th>
								<td>
									<fieldset>
										<label for="wp_player_options[jQuery]">
											<input type="checkbox" id="wp_player_options[jQuery]" name="wp_player_options[jQuery]" value="true"  <?php if( $options['jQuery'] == 'true' ) echo 'checked="checked"'; ?> />
											点击选中 jQuery (<small>有些主题以自带jQuery库，如已有则取消此选项</small>)
										</label>
									</fieldset>						
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="blogname">解析虾米音乐 URL <small style="font-weight:normal;">(推荐新浪云)</small></label></th>
								<td>
								<?php echo $options['api']; ?>
									<fieldset>
										<label>
											<input type="radio" name="wp_player_options[api]" value="wpplayer.sinaapp.com" <?php if( $options['api'] == 'wpplayer.sinaapp.com' ) echo 'checked="checked"'; ?> />
											新浪云：<code>wpplayer.duapp.com</code>
										</label>
										&nbsp;&nbsp;
										<label>
											<input type="radio" name="wp_player_options[api]" value="api.webjyh.com" <?php if( $options['api'] == 'api.webjyh.com' ) echo 'checked="checked"'; ?> />
											备用API：<code>api.webjyh.com</code> <small>(不推荐)</small>
										</label>
									</fieldset>						
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="blogname">选择播放器皮肤</label></th>
								<td>
									<fieldset>
										<label>
											<input type="radio" name="wp_player_options[themes]" value="1" <?php if( $options['themes'] == 1 ) echo 'checked="checked"'; ?> />
											默认皮肤
										</label>
										&nbsp;&nbsp;
										<label>
											<input type="radio" name="wp_player_options[themes]" value="2" <?php if( $options['themes'] == 2 ) echo 'checked="checked"'; ?> />
											扁平化风格
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