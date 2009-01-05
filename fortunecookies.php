<?php
/*
Plugin Name: Fortune Cookies
Plugin URI: http://fortunecookies.in/wp/
Description: Gets you a random fortune cookie quote in your Wordpress sidebar from the database of over 12,000 fortunes (based on the Unix Fortune program). You need to register and get an appid from http://fortunecookies.in/registerws.php. Once you get the appid, update it in the Widget Options.
Version: 1.0
Author: Fortune
Author URI: http://fortunecookies.in

Copyright (C) 2009  zeus <zeus@fortunecookies.in>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


add_action('plugins_loaded','fcookie_plugin_init');

//-------------------------
// Register hooks
//-------------------------
function fcookie_plugin_init()
{
  if ( function_exists('wp_register_sidebar_widget') ) {
	$widget_ops = array('classname' => 'widget_fcookie', 'description' => __( "Gets you a random fortune cookie quote.") );
    wp_register_sidebar_widget('fcookie', __('Fortune Cookies'), 'fcookie_sidebar_widget', $widget_ops);
  }
  if ( function_exists('wp_register_widget_control') ) {
  	wp_register_widget_control('fcookie', __('Fortune Cookies'), 'fcookie_widget_control' );
  }
}

//-------------------------
// Widget control
//-------------------------
function fcookie_widget_control() {

  if($_POST['widget-fcookie-submit'])
  {
    $options['title'] = strip_tags(stripslashes($_POST['widget-fcookie-title']));
    $options['appid'] = strip_tags(stripslashes($_POST['widget-fcookie-appid']));
    update_option('widget_fcookie', $options);
  }

  $options = get_option('widget_fcookie');

  if(!is_array($options))
  {
    $options = array('title' => 'Fortune Cookies','appid' => '');
  }

  $title = htmlspecialchars($options['title'], ENT_QUOTES);
  $appid = $options['appid'];

  echo '<p> <label for="widget-fcookie-title">' . __('Title: ') . '<input class="widefat" id="widget-fcookie-title" name="widget-fcookie-title" type="text" value="' . $title . '" /></label>';
  echo '<p> <label for="widget-fcookie-appid">' . __('Appid: ') . '<input class="widefat" id="widget-fcookie-appid" name="widget-fcookie-appid" type="text" value="' . $appid. '" /></label> </p>';
  echo '<input type="hidden" id="widget-fcookie-submit" name="widget-fcookie-submit" value="1" />';
}

//-------------------------
// The sidebar widget
//-------------------------
function fcookie_sidebar_widget($args) 
{
  // Get options
  $options = get_option('widget_fcookie');

  // Set defaults 
  if( ! isset($options['title']) || $options['title'] == "" )
  {
    $options['title'] = 'Fortune Cookies';
  }


  $title = htmlspecialchars($options['title']); 
  $fortune = get_random_fortune($options['appid']);

  //apply filters
  $title = apply_filters('widget_title', $title);
  $fortune = apply_filters( 'widget_text', $fortune);
  
  // extracts before_widget,before_title,after_title,after_widget
  extract($args); 
  echo $before_widget . $before_title . $title . $after_title ;
  echo "<div>";
  echo "$fortune";
  echo "</div>";
  echo $after_widget;
}

//-------------------------
// Gets random cookie from fortunecookies.in
// Input: appid
// appid can be obtained @ http://fortunecookies.in/registerws.php
//-------------------------

function get_random_fortune($appid) {
	$fortune = 'Update a valid appid in the fortunecookies wordpress widget options.
				Appid can be obtained by registering <a href="http://fortunecookies.in/registerws.php"> here </a>';
    
	$ch = curl_init("http://fortunecookies.in/ws.php?appid=".trim($appid));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$str = curl_exec($ch);
	$doc = domxml_open_mem($str);
	$cookie = $doc->document_element();
	
	foreach ($cookie->child_nodes() as $node) {
	  if ( $node->node_name() == 'msg' ) {
		$fortune = htmlspecialchars($node->get_content());
	  } else if ( $node->node_name() == 'author' ) {
		$author = htmlspecialchars($node->get_content());
	  }
	}

	curl_close($ch);
    
    if ($author != "") {
	  $fortune .= "<br> <i> -- ". $author . "</i>";
	}

	return nl2br($fortune);
}

?>