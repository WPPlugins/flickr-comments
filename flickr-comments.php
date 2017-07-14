<?php

/*  Copyright 2013  jdleung  (email : jdleungs@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**

Plugin Name: Flickr Comments
Plugin URI: http://wordpress.org/extend/plugins/flickr-comments/
Description: Retrieves comments from your Flickr account to your Wordpress photoblog in a specified Time Frame and Time Interval.   
Author: jdleung
Version: 1.23
Author URI: http://www.jdleungs.com

**/

$timezone = get_option('timezone_string');
date_default_timezone_set($timezone);

$d_date_format = get_option('date_format');
$d_time_format = get_option('time_format');
$datetime_format = " ".$d_date_format." - ".$d_time_format;

if($_GET['frob']){
	$admin_url = admin_url();
	$fc_api = get_option('flickr_comments_API');
	require_once('phpFlickr/phpFlickr.php');
    $f = new phpFlickr($fc_api['api_key'], $fc_api['api_secret']);
	$f->auth_getToken($_GET['frob']);

	if(!empty($_SESSION['phpFlickr_auth_token'])){
		$flickr_comments_API['api_key']= $fc_api['api_key'];
		$flickr_comments_API['api_secret']= $fc_api['api_secret'];
		$flickr_comments_API['token']= $_SESSION['phpFlickr_auth_token'];
		update_option('flickr_comments_API', $flickr_comments_API);
		header('location:'.$admin_url.'options-general.php?page=flickr-comments/flickr-comments.php');
		exit;
	}
}


// add the admin options page
add_action('admin_menu', 'plugin_admin_add_page');

function plugin_admin_add_page() {
	
	if($_POST['flickr_comments_API']){
		$flickr_comments_API['api_key']= $_POST['flickr_comments_API']['api_key'];
		$flickr_comments_API['api_secret']= $_POST['flickr_comments_API']['api_secret'];
		update_option('flickr_comments_API', $flickr_comments_API);
		
		$fc_config = get_option('flickr_comments_config');
		if(empty($fc_config['timeframe']) && empty($fc_config['interval'])){
			$flickr_comments_config['timeframe'] = '7d';
			$flickr_comments_config['interval'] = 36000;
			$flickr_comments_config['allow_html'] = 'checked';
			$flickr_comments_config['lastupdate'] = time();
			update_option('flickr_comments_config',$flickr_comments_config);
		}

		require_once('phpFlickr/phpFlickr.php');
		unset($_SESSION['phpFlickr_auth_token']);
		$f = new phpFlickr($flickr_comments_API['api_key'], $flickr_comments_API['api_secret']);
		$f->auth("read", false );
	
	}

	if($_POST['manual_update'])
		flcikr_comments_mupdate($_POST['manual_update']['input']);
	
	add_options_page('Flickr Comments', 'Flickr Comments', 'manage_options', __FILE__, 'flickr_comments_config_page');
}

// add the admin settings and such
add_action('admin_init', 'plugin_admin_init');

function plugin_admin_init(){
	
	register_setting( 'flickr_comments_API', 'flickr_comments_API', '' );
	add_settings_section('api_main', '<h3>1. Flickr API:</h3>', '', 'api');
	add_settings_field('api_key', 'API key', 'api_key_input', 'api', 'api_main');
	add_settings_field('api_secret', 'API secret', 'api_secret_input', 'api', 'api_main');	

	register_setting( 'custom_field', '', '' );
	add_settings_section('custom_field_main', '<h3>2. Set Custom Field</h3>', '', 'custom_field');
	add_settings_field('custom_field_input', '', 'custom_field_input', 'custom_field', 'custom_field_main');	

	register_setting( 'manual_update', '', '' );
	add_settings_section('manual_update_main', '<h3>3. Manual-update</h3>', '', 'mupdate');
	add_settings_field('manual_update_input', 'Flikcr Photo ID', 'manual_update_input', 'mupdate', 'manual_update_main');	

	register_setting( 'flickr_comments_config', 'flickr_comments_config', 'flickr_comments_config_validate' );
	add_settings_section('fc_config_main', '<h3>4. Auto-update Settings:</h3>', '', 'fc_config');
	add_settings_field('timeframe', 'Time frame', 'timeframe_config', 'fc_config', 'fc_config_main');
	add_settings_field('interval', 'Time interval', 'interval_config', 'fc_config', 'fc_config_main');
	add_settings_field('allow_html', 'Allow HTML', 'allow_html_config', 'fc_config', 'fc_config_main');
	add_settings_field('allow_img', 'Allow Image', 'allow_img_config', 'fc_config', 'fc_config_main');
	add_settings_field('lastupdate', '', 'lastupdate_config', 'fc_config', 'fc_config_main');
}


// display the admin options page
function flickr_comments_config_page() {
	
	$fc_api = get_option('flickr_comments_API');

	echo "<h1>Flickr Comments by <a href='http://www.jdleungs.com/' target='_blank'>jdleung</a></h1><br>";
	echo "<form action='options.php' method='post'>";
	 settings_fields('flickr_comments_API'); 
	 do_settings_sections('api'); 
	echo "<input name='get_token' type='submit' value='Get Token' /></form>";		
		
	if($fc_api['token']){
		echo "<hr size=1>";
		 // settings_fields('custom_field'); 
		 do_settings_sections('custom_field'); 


		echo "<hr size=1>";
		echo "<form action='options.php' method='post'>";
		 settings_fields('manual_update'); 
		 do_settings_sections('mupdate'); 
		echo "<input name='Submit' type='submit' value='DO IT' /></form><br><hr size=1>";		
			  
		echo "<form action='options.php' method='post'>";
		 settings_fields('flickr_comments_config'); 
		 do_settings_sections('fc_config'); 
		echo "<input name='Submit' type='submit' value='Save setting' /></form><br>";
	}
}

function api_key_input() {
	$fc_api = get_option('flickr_comments_API');
	echo "<input id='api_key' name='flickr_comments_API[api_key]' size='35' type='text' value='{$fc_api['api_key']}' />";
} 

function api_secret_input() {
	$fc_api = get_option('flickr_comments_API');
	$site_url = site_url();
	echo "<input id='api_secret' name='flickr_comments_API[api_secret]' size='20' type='text' value='{$fc_api['api_secret']}' /><br>";
	echo "<font color='#777777'>Both API key and secret are needed. You can <a href='http://www.flickr.com/services/api/keys/'>Get Another Key</a> from Flickr. (Sign in first)<br><br />IMPORTANT: Edit the authentication flow to set its Callback URL to your site url: \"".$site_url."\".</font><br>";
	if($fc_api['token']){
		echo "<br /><font color=red>First step done! </font><font color='#777777'> NO NEED to do again unless you want to use another Flickr API key.</font><br>";
	}	
} 

function custom_field_input() {
	echo "<font color='#777777'>Add a custom field in wordpress post called \"flickr_photo_id\" ( See <a href='http://codex.wordpress.org/Custom_Fields' target='_blank'>how to create Custom field</a> ). <br /><br />Input the Flickr Photo ID need to be retrieved. ( Flickr Photo ID is usually the number in the URL of a photo page eg: <a href='http://www.flickr.com/photos/jdleung/540162262/' target='_blank'>http://www.flickr.com/photos/jdleung/540162262/</a> )
</font>";
} 

function manual_update_input() {
	echo "<input id='manual_update_input' name='manual_update[input]' size='15' type='text' value='' /><br>";
	echo "<li>One ID for one photo.</li>";
	echo "<li>Blank for all photos.</li>";
	echo "<br /><font color='#777777'>Only the wordpress posts with Custom field \"flickr_photo_id\" will be updated. </font><br />";
	echo "<br /><font color='#777777'>It's important to do a manual-update for the first time! By the API Method that Auto-update uses, Flickr only allows retrieving the comments created within 200 days! </font><br>";	
	echo "<br /><font color='#777777'>Manual-update can read all comments for the post with 'flickr_photo_id'. <br>Be patient! It may take a long time to go, depending on the amount of photos and comments.</font><br>";
} 

function timeframe_config() {
	$fc_config = get_option('flickr_comments_config');
	echo "<input id='timeframe' name='flickr_comments_config[timeframe]' size='8' type='text' value='{$fc_config['timeframe']}' /><br>";
	echo "<br /><font color='#777777'>The timeframe in which to return updates for. This can be specified in days ('2d') or hours ('4h'). <br>It means it reads comments created within 2 days or 4 hours. Too big number will be replacedd with the highest limit '200d' or '4800h'.</font><br>";
	echo "<br /><font color='#777777'>The bigger number takes the longer time. Recommends: No more than 30 days. Invalid format input will be replaced by '7d'.</font><br>"; 
	echo "<br /><font color='#777777'>Using manual-update to read comments created more than 200 days.</font>";
} 

function interval_config() {
	$fc_config = get_option('flickr_comments_config');
	echo "<input id='interval' name='flickr_comments_config[interval]' size='8' type='text' value='{$fc_config['interval']}' /><br>";
	echo "<font color='#777777'>Numbers ONLY. By default, it's 36000 seconds(10 hours). It means it reads comments from Flickr every 10 hours.</font>";
} 

function allow_html_config() {
	$fc_config = get_option('flickr_comments_config');
	echo "<input id='allow_html' name='flickr_comments_config[allow_html]' type='checkbox' value='checked' {$fc_config['allow_html']} /><br>";
} 

function allow_img_config() {
	$fc_config = get_option('flickr_comments_config');
	echo "<input id='allow_img' name='flickr_comments_config[allow_img]' type='checkbox' value='checked' {$fc_config['allow_img']} /><br>";
} 

function lastupdate_config() {
	global $datetime_format;

	$fc_config = get_option('flickr_comments_config');
	$interval = $fc_config['interval'];
	$last_update_timestamp =$fc_config['lastupdate'];	

	if(!is_numeric($fc_config['lastupdate']))
		$last_update_timestamp = strtotime($fc_config['lastupdate']);

	$timezone = get_option('timezone_string');
	date_default_timezone_set($timezone);	
	
	$next_update_timestamp = $last_update_timestamp+$interval;
	$last_update_time = date($datetime_format, $last_update_timestamp);
	$next_update_time = date($datetime_format, $next_update_timestamp);

	echo "<input id='lastupdate' name='flickr_comments_config[lastupdate]' size='20' type='hidden' value='{$last_update_timestamp}' />";
	echo "<font color='red'>Last update: ".$last_update_time." &nbsp;&nbsp;Next update: ".$next_update_time."</font>";
} 

// validate our options
function flickr_comments_config_validate($input) {
	$newinput['timeframe'] = strtolower(trim($input['timeframe']));
	$newinput['interval'] = trim($input['interval']);
	$newinput['allow_html'] = $input['allow_html'];
	$newinput['allow_img'] = $input['allow_img'];
	$newinput['lastupdate'] = $input['lastupdate'];

	if(!preg_match('/(^[1-9]\d+)(H|h|D|d)$/', $newinput['timeframe'])) 
		$newinput['timeframe'] = '7d';
		
	$timeframe_format = substr($newinput['timeframe'], -1);	

	if($timeframe_format == 'd'){
		$timeframe_num = trim($newinput['timeframe'], 'd');
		if($timeframe_num > 200)
			$newinput['timeframe'] = '200d';
	}
	
	if($timeframe_format == 'h'){
		$timeframe_num = trim($newinput['timeframe'], 'h');
		if($timeframe_num > 4800)
			$newinput['timeframe'] = '4800h';
	}
	
	if(!preg_match('/^[1-9]\d+$/', $newinput['interval'])) 
		$newinput['interval'] = '36000';
	
	return $newinput;
}

add_filter( 'plugin_action_links', 'fc_config_links', 10, 2 );

// Display a Settings link on the main Plugins page
function fc_config_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$fc_links = '<a href="'.get_admin_url().'options-general.php?page=flickr-comments/flickr-comments.php">'.__('Settings').'</a>';
		
		// make the 'Settings' link appear first
		array_unshift( $links, $fc_links );
	}

	return $links;
}


function flcikr_comments_mupdate($manual_update_input){
	global $wpdb;
	
	$fc_api = get_option('flickr_comments_API');
	require_once('phpFlickr/phpFlickr.php');
	$f = new phpFlickr($fc_api['api_key'], $fc_api['api_secret']);
	set_time_limit(0); 

	if($manual_update_input) 
		$meta_infos = $wpdb->get_results( "SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key='flickr_photo_id' AND meta_value='{$manual_update_input}'");
	else
		$meta_infos = $wpdb->get_results( "SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key='flickr_photo_id' ");	

	if($meta_infos){ 	
		foreach ( $meta_infos as $meta_info ) {
			$post_id = $meta_info->post_id;
			$flickr_photo_id = $meta_info->meta_value;
			$comments   = $f->photos_comments_getList($flickr_photo_id); 
			if($comments['comments']['comment']) {
				foreach( $comments['comments']['comment'] as $comment ) {
					$f_commentdate = $comment['datecreate'];						
					$f_url = "http://www.flickr.com/photos/".$comment['author'];
					$f_name = $comment['authorname'];
					$f_comment = $comment['_content'];	
					handle_comments($post_id,$f_commentdate,$f_url,$f_name,$f_comment,$f);
				}
			}
		}
	}
	
	$timezone = get_option('timezone_string');
	date_default_timezone_set($timezone);
	$fc_config = get_option('flickr_comments_config');
	$flickr_comments_config['timeframe']= $fc_config['timeframe'];
	$flickr_comments_config['interval']= $fc_config['interval'];
	$flickr_comments_config['allow_html']= $fc_config['allow_html'];
	$flickr_comments_config['allow_img']= $fc_config['allow_img'];
	$flickr_comments_config['lastupdate']= time();
	update_option('flickr_comments_config', $flickr_comments_config);

}	// end flickr_comments_mupdate


add_action('wp_head', 'flickr_comments');

function flickr_comments(){
	global $wpdb;

	$timezone = get_option('timezone_string');
	date_default_timezone_set($timezone);	
	$fc_config = get_option('flickr_comments_config');
	$timeframe = $fc_config['timeframe'];
	$interval = $fc_config['interval'];
	$allow_html = $fc_config['allow_html'];
	$allow_img = $fc_config['allow_img'];
	$lastupdate =$fc_config['lastupdate'];
	$curr_timestamp = time();

	if(!$lastupdate)
		$last_timestamp = $curr_timestamp;
		
	$update = ($last_timestamp+$interval)-$curr_timestamp;

	if( $update <= 0){	
		$fc_api = get_option('flickr_comments_API');
		require_once('phpFlickr/phpFlickr.php');
		$_SESSION['phpFlickr_auth_token'] = $fc_api['token'];
		$f = new phpFlickr($fc_api['api_key'], $fc_api['api_secret']);
		$result_page = 1;

		do{
			$comments_info = $f->activity_userPhotos($timeframe,50,$result_page);
	 		$total_pages = $comments_info['pages'];
	 		foreach ((array)$comments_info['item'] as $key=>$c) {
			 	$total_comments = $c['comments'];
				$flickr_photo_id = $c['id'];
				$meta_infos = $wpdb->get_row( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='flickr_photo_id' AND meta_value='{$flickr_photo_id}' ");	
				$post_id = $meta_infos->post_id;					

				if($post_id){	
					$comment_count = 0;
					foreach((array)$c['activity']['event'] as $key2=>$d){
	
						$type = $d['type'];
						if($type == "comment" && $c['type'] == "photo"){ // show only comments
							$f_url = "http://www.flickr.com/photos/".$d['user'];
							$f_commentdate = $d['dateadded'];
							$f_name = $d['username'];
							$f_comment = $d['_content'];
							handle_comments($post_id,$f_commentdate,$f_url,$f_name,$f_comment,$f);
							$comment_count++;
						}
					} 
				
					if($comment_count >= 10){ //photo has more than 10 recent comments

						set_time_limit(0); 
						$timeframe_format = substr($timeframe,-1);	
				
						if($timeframe_format == 'd')
							$timeframe_second = trim($timeframe,'d')*3600*24;
					
						if($timeframe_format == 'h')
							$timeframe_second = trim($timeframe,'h')*3600; 
					
						$max_comment_date = $curr_timestamp;
						$min_comment_date = $curr_timestamp-$timeframe_second;
				
						$comments = $f->photos_comments_getList($flickr_photo_id, $min_comment_date, $max_comment_date); 
						if($comments['comments']['comment']) {
							foreach( $comments['comments']['comment'] as $comment ) {
								$f_commentdate = $comment['datecreate'];	
								$f_url = "http://www.flickr.com/photos/".$comment['author'];
								$f_name = $comment['authorname'];
								$f_comment = $comment['_content'];	
								handle_comments($post_id,$f_commentdate,$f_url,$f_name,$f_comment,$f);
							}
						}
									
					} //end more
				}

			}//end foreach
	
			$result_page++;
	
		} while ($result_page <= $total_pages);

		$flickr_comments_config['timeframe']= $timeframe;
		$flickr_comments_config['interval']= $interval;
		$flickr_comments_config['allow_html']= $allow_html;
		$flickr_comments_config['allow_img']= $allow_img;
		$flickr_comments_config['lastupdate']= time();
		update_option('flickr_comments_config', $flickr_comments_config);
	}//end update

}// end flickr_comments

			
function handle_comments($post_id,$f_commentdate,$f_url,$f_name,$f_comment,$f){
	global $wpdb;

	$timezone = get_option('timezone_string');
	date_default_timezone_set($timezone);
	$f_comment_datetime = date ('Y-m-d H:i:s',$f_commentdate);

	// Is the comment already in the database?
	$check_dupes = $wpdb->get_results( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_date='{$f_comment_datetime}'", ARRAY_N);
	$has_dupes = count($check_dupes);

	if(empty($has_dupes)){ 

		//insert comment log
		$log_datetime = date('Y-m-d H:i:s');
		$checkdupe = var_export($check_dupes, true);
		$wpdb->insert( 'comment_log', array( 'author' => $f_name, 'comment' => $f_comment, 'date' => $f_comment_datetime, 'checkdupe' => $checkdupe, 'hasdupe' => $has_dupes, 'log_datetime' => $log_datetime ) ); 
	
		$fc_config = get_option('flickr_comments_config');
		
		if( preg_match("/(\[)(http.+photos\/.+)(\])/i",$f_comment,$matches2)){ 	//reply user icon from [flickr] to <img>
			$user_link = $matches2[2];
			$user = $f->urls_lookupUser($user_link); //get user id from link
			$user_info = $f->people_getInfo($user['id']); //get user info from id
			$f_comment = preg_replace("/(\[)(http.+photos\/.+)(\])/i", "<a href='".$user_link."'><img src='http://farm".$user_info['iconfarm'].".staticflickr.com/".$user_info['iconserver']."/buddyicons/".$user['id'].".jpg?3334118#".$user['id']."' alt='' width='24' height='24' border='0'></a>" ,$f_comment); 
		}		

		if(empty($fc_config['allow_html']))
			$f_comment = strip_tags($f_comment, "<img>"); 

		if(empty($fc_config['allow_img'])){
			//reply user icon to text [username]
			if(preg_match('/(<img.*src.*staticflickr.*buddyicons.*#)(\d+@N\d+)(\'.*alt.*width.*>)/i', $f_comment, $matches3)){
				$user_link = "http://www.flickr.com/photos/".$matches3[2];
				$user = $f->urls_lookupUser($user_link);
				$f_comment = preg_replace('/<img.*src.*staticflickr.*buddyicons.*[^a]>/i', '['.$user['username'].']', $f_comment);		
			}
			$f_comment = strip_tags($f_comment, "<a>"); 	
		}

		$f_comment = preg_replace('/<a href=\'\/photos\//', '<a href=\'http://www.flickr.com/photos/',$f_comment);
		
		$flickr_comm_data['comment_post_ID'] = $post_id;
		$flickr_comm_data['comment_author'] = $f_name;
		// $flickr_comm_data['comment_author_email'] = 'admin@admin.com';
		$flickr_comm_data['comment_author_url'] = $f_url;
		$flickr_comm_data['comment_content'] = $f_comment;
		// $flickr_comm_data['comment_author_IP'] = '127.0.0.1';
		$flickr_comm_data['comment_agent'] = 'Flickr Comments';
		$flickr_comm_data['comment_date'] = $f_comment_datetime;
		$flickr_comm_data['comment_approved'] = 1;

		wp_insert_comment($flickr_comm_data);			
	}

}//end handle_comment()




?>
