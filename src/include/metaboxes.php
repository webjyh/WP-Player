<?php

/* Add a new meta box to the admin menu. */
add_action( 'admin_menu', 'wp_player_create_meta_box' );

/* Saves the meta box data. */
add_action( 'save_post', 'wp_player_save_meta_data' );

/**
 * Function for adding meta boxes to the admin.
 * Separate the post and page meta boxes.
 */

function wp_player_create_meta_box() {
    global $theme_name;
    add_meta_box( 'wp-post-meta-boxes', 'WP-Player 播放器选项', 'wpPlayer_post_meta_boxes', 'post', 'normal', 'high' );
    add_meta_box( 'wp-page-meta-boxes', 'WP-Player 播放器选项', 'wpPlayer_post_meta_boxes', 'page', 'normal', 'high' );
}

/**
 * Array of variables for post meta boxes.  Make the 
 * function filterable to add options through child themes.
 */
function wp_player_meta_boxes( $val = true, $apply = false ) {
    if ( function_exists('curl_init') ) {
        $arr1 = array(
            'wp_player_music_type' => array(
                    'name' => 'wp_player_music_type',
                    'type' => 'select',
                    'options' => array(
                        'netease' => '网易音乐',
                        'xiami' => '虾米音乐',
                        'tencent' => 'QQ 音乐',
                        'baidu' => '百度音乐'
                    ),
                    'output' => false
            ),
            'mp3_xiami_type' => array(
                    'name' => 'mp3_xiami_type',
                    'type' => 'select',
                    'options' => array(
                        'song' => '歌曲页面',
                        'album' => '专辑页面',
                        'artist' => '歌手页面',
                        'collect' => '歌单页面'
                    ),
                    'output' => false
            ),
            'wp_player_lyric_open' => array(
                'name' => 'wp_player_lyric_open',
                'type' => 'select',
                'options' => array(
                    'close' => '关闭歌词',
                    'open' => '开启歌词'
                ),
                'output' => false
            ),
            'mp3_xiami' => array(
                    'name' => 'mp3_xiami',
                    'type' => 'text',
                    'description' => '即可填音乐写ID，也可填写音乐网址 http://......',
                    'button' => '获取音乐ID',
                    'output' => false
            )
        );
    } else {
        $arr1 = array();
    }

    $arr2 = array(
        'mp3_title' => array(
            'name' => 'mp3_title',
            'type' => 'textarea',
            'title' => '歌曲名',
            'description' => '请填写歌曲名，一行一个歌曲名，用于列表展示显示。',
            'output' => true
        ),
        'mp3_author' => array(
            'name' => 'mp3_author',
            'type' => 'textarea',
            'title' => '歌手名',
            'description' => '请填写歌手名，一行一个歌手名，请与上面的歌曲名一一对应',
            'output' => true
        ),
        'mp3_address' => array(
            'name' => 'mp3_address',
            'type' => 'upload',
            'title' => '歌曲地址',
            "description" => '请填写歌曲地址，可以上传歌曲，也可以用链接地址（请记得带上http://），一行一个歌曲地址，请与上面的歌曲名一一对应。',
            'output' => true
        ),
        'mp3_thumb' => array(
            'name' => 'mp3_thumb',
            'type' => 'upload',
            'title' => '歌曲封面',
            "description" => '上传封面，可以为空，WP-Player 将使用默认图，上传图片时记得与歌曲名一一对应，如果此歌曲默认封面，则此行留空。',
            'output' => true
        )
    );
    $meta_boxes = $val ? $arr1 : $arr2;

    if ( $apply ){
        $meta_boxes = array();
        $meta_boxes = array_merge( $arr1, $arr2 );
    }

    return apply_filters( 'wp_player_meta_boxes', $meta_boxes );
}


/**
 * get meta boxes 
 */
function get_wp_player_metaBox( $val = true ){
	global $post;
	$meta_boxes = wp_player_meta_boxes($val);
	foreach ( $meta_boxes as $meta ){
		$value = get_post_meta( $post->ID, $meta['name'], true );
		switch ($meta['type']) {
			case 'text': wp_player_get_meta_text_input( $meta, $value ); break;
			case 'select': wp_player_get_meta_select( $meta, $value ); break;
            case 'textarea': wp_player_get_meta_textarea( $meta, $value ); break;
			case 'upload': wp_player_get_meta_upload( $meta, $value ); break;
			case 'button': wp_player_get_meta_button( $meta, $value ); break;
		}
	}
}

/**
 * Displays meta boxes on the Write Post panel.  Loops 
 * through each meta box in the $meta_boxes variable.
 * Gets array from solostream_post_meta_boxes().
 */

function wpPlayer_post_meta_boxes() {
    global $post; ?>
    <div class="wp-player-wrap" id="wp-player-wrap">
        <ul class="wp-player-tabs" id="wp-player-tabs">
            <li class="current"><a href="javascript: void(0);">云歌曲网址</a></li>
            <li><a href="javascript: void(0);">手动上传</a></li>
        </ul>
        <div class="wp-player-row" id="wp-player-row">
            <div class="wp-player-inner current">
                <div class="wp-player-input"><?php
                    if (function_exists('curl_init')) {
                        get_wp_player_metaBox();
                    } else {
                        echo '<div>您的站点当前不支持此功能</div>';
                    }
                ?></div>
            </div>
            <div class="wp-player-inner"><?php get_wp_player_metaBox(false); ?></div>
        </div>
    </div>
<?php
}

/**
 * Outputs a text input box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */

function wp_player_get_meta_text_input( $args = array(), $value = false ) {

    extract( $args );

    $html .= $output ? '<div class="wp-player-input"><p>'.$title.'</p><p>' : "\n";
    $html .= '<input type="text" name="'.$name.'" id="'.$name.'" value="'.esc_html( $value ).'" title="'.$description.'" class="wp-player-text" placeholder="'.$description.'" />';
    $html .= '<input type="hidden" name="'.$name.'_noncename" id="'.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
    if ( $button ){
        $html .= "\n".'<button id="wp_player_get_xiami_id" type="button" class="button wp-player-button">'.$button.'</button>';
        $html .= "\n".'<a href="'.site_url( '/wp-admin/options-general.php?page=player.php' ).'" class="button button-primary wp-player-primary">使用说明</a>';
    }
    $html .= $output ? '</p></div>'."\n" : "\n";

    echo $html;
}

/**
 * Outputs a select box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_select( $args = array(), $value = false ) {

    extract( $args );

    $html .= $output ? '<div class="wp-player-input"><p>'.$title.'</p><p>' : "\n";
    $html .= '<select class="wp-player-select" name="'.$name.'" id="'.$name.'">';
    foreach ( $options as $key => $option ){
        $selected = ($key == $value) ? 'selected="selected"' : '';
        $html .= '<option value="'.$key.'" '.$selected.'>'.$option.'</option>';
    }
    $html .= '<input type="hidden" name="'.$name.'_noncename" id="'.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
    $html .= $output ? '</p></div>'."\n" : "\n";

    echo $html;
}

/**
 * Outputs a textarea box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_textarea( $args = array(), $value = false ) {

    extract( $args );
    
    $html .= '<div class="wp-player-textarea"><p><strong>'.$title.'</strong></p>';
    $html .= '<textarea name="'.$name.'" id="'.$name.'">'.esc_html($value).'</textarea>'."\n";
    $html .= '<p class="desc">'.$description.'</p>';
    $html .= '<input type="hidden" name="'.$name.'_noncename" id='.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
    $html .= '</div>';
    
    echo $html;
}

/**
 * Outputs a Upload box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_upload( $args = array(), $value = false ) {

    extract( $args ); 

    $html .= '<div class="wp-player-textarea"><p><strong>'.$title.'</strong></p>';
    $html .= '<textarea name="'.$name.'" id="'.$name.'">'.esc_html($value).'</textarea>'."\n";
    $html .= '<input id="'.$name.'_button" type="button" class="WP-Player-File button-secondary" value="点击上传" />';
    $html .= '<p class="desc">'.$description.'</p>';
    $html .= '<input type="hidden" name="'.$name.'_noncename" id='.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
    $html .= '</div>'."\n";

    echo $html;
}

/**
 * Loops through each meta box's set of variables.
 * Saves them to the database as custom fields.
 */

function wp_player_save_meta_data( $post_id ) {
    global $post;

    $meta_boxes = wp_player_meta_boxes( true, true );

    foreach ( $meta_boxes as $meta_box ){
        
        if ( 'page' == $_POST['post_type'] && !current_user_can( 'edit_page', $post_id ) ){
            return $post_id; 
        }

        if ( 'post' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) ){
            return $post_id;
        }

        if ( !wp_verify_nonce( $_POST[$meta_box['name'] . '_noncename'], plugin_basename( __FILE__ ) ) ){
            return $post_id;
        }

        $data = stripslashes( $_POST[$meta_box['name']] );

        if ( get_post_meta( $post_id, $meta_box['name'] ) == '' ){
            add_post_meta( $post_id, $meta_box['name'], $data, true );
        } elseif ( $data != get_post_meta( $post_id, $meta_box['name'], true ) ){
            update_post_meta( $post_id, $meta_box['name'], $data );
        }elseif ( $data == '' ){
            delete_post_meta( $post_id, $meta_box['name'], get_post_meta( $post_id, $meta_box['name'], true ) );
        }

    }

}
?>