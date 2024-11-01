<?php  
/* 
Plugin Name: SpotLighter 
Plugin URI: http://omniwp.com/plugins/spotligher-a-wordpress-plugin/ 
Description: With Spotlighter you can direct the user's attention to wherever you want.
Version: 0.5
Author: Nimrod Tsabari / omniWP
Author URI: http://omniwp.com
*/  
/*  Copyright 2012  Nimrod Tsabari / omniWP  (email : yo@omniwp.com)

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

define('SPOTLIGHTER_VER', '0.5');
define('SPOTLIGHTER_DIR', plugin_dir_url( __FILE__ ));

/* SpotLighter : Init */
/* ---------------- */

function init_spotlighter() {

	wp_register_style('spotlighter-style', SPOTLIGHTER_DIR . 'css/spotlighter.css');
	wp_enqueue_style('spotlighter-style');
	wp_register_script('spotlighter-script', SPOTLIGHTER_DIR . 'js/spotlighter.min.js', array('jquery'));
	wp_enqueue_script('spotlighter-script');
}

add_action('wp_enqueue_scripts', 'init_spotlighter');

/* Spotlighter : Activation */
/* -------------------- */

define('SPOTLIGHTER_NAME', 'Spotlighter');
define('SPOTLIGHTER_SLUG', 'spotlighter_registration');

register_activation_hook(__file__,'omni_spotlighter_admin_activate');
add_action('admin_notices', 'omni_spotlighter_admin_notices');	

function omni_spotlighter_admin_activate() {
	$reason = get_option('omni_plugin_reason');
	if ($reason == 'nothanks') { 
		update_option('omni_plugin_on_list',0);
	} else {		
		add_option('omni_plugin_on_list',0);
		add_option('omni_plugin_reason','');
	}
}

function omni_spotlighter_admin_notices() {
	if ( get_option('omni_plugin_on_list') < 2 ){		
		echo "<div class='updated'><p>" . sprintf(__('<a href="%s">' . SPOTLIGHTER_NAME . '</a> needs your attention.'), "options-general.php?page=" . SPOTLIGHTER_SLUG). "</p></div>";
	}
} 

/*  Spotlighter : Admin Part  */
/* --------------------- */
/* Inspired by Purwedi Kurniawan's SEO Searchterms Tagging 2 Pluging */

function spotlighter_admin() {
	if (omni_spotlighter_list_status()) omni_spotlighter_thank_you(); 
}            

function spotlighter_admin_init() {
	$onlist = get_option('omni_plugin_on_list');
	if ($onlist < '2') add_options_page("Spotlighter| Registration", "Spotlighter| Registration", 1, "spotlighter_registration", "spotlighter_admin");
}

add_action('admin_menu', 'spotlighter_admin_init');

function omni_spotlighter_thank_you() {
	wp_redirect(admin_url());
}

function omni_spotlighter_list_status() {
	$onlist = get_option('omni_plugin_on_list');
	$reason = get_option('omni_plugin_reason');
	if ( trim($_GET['onlist']) == 1 || $_GET['no'] == 1 ) {
		$onlist = 2;
		if ($_GET['onlist'] == 1) update_option('omni_plugin_reason','onlist');
		if ($_GET['no'] == 1) {
			 if ($reason != 'onlist') update_option('omni_plugin_reason','nothanks');
		}
		update_option('omni_plugin_on_list', $onlist);
	} 
	if ( ((trim($_GET['activate']) != '' && trim($_GET['from']) != '') || trim($_GET['activate_again']) != '') && $onlist != 2 ) { 
		update_option('omni_plugin_list_name', $_GET['name']);
		update_option('omni_plugin_list_email', $_GET['from']);
		$onlist = 1;
		update_option('omni_plugin_on_list', $onlist);
	}
	if ($onlist == '0') {
		if (isset($_GET['noheader'])) require_once(ABSPATH . 'wp-admin/admin-header.php');
		omni_spotlighter_register_form_1('spotlighter_registration');
	} elseif ($onlist == '1') {
		if (isset($_GET['noheader'])) require_once(ABSPATH . 'wp-admin/admin-header.php');
		$name = get_option('omni_plugin_list_name');
		$email = get_option('omni_plugin_list_email');
		omni_spotlighter_do_list_form_2('spotlighter_confirm',$name,$email);
	} elseif ($onlist == '2') {
		return true;
	}
}

function omni_spotlighter_register_form_1($fname) {
	global $current_user;
	get_currentuserinfo();
	$name = $current_user->user_firstname;
	$email = $current_user->user_email;
?>
	<div class="register" style="width:50%; margin: 100px auto; border: 1px solid #BBB; padding: 20px;outline-offset: 2px;outline: 1px dashed #eee;box-shadow: 0 0 10px 2px #bbb;">
		<p class="box-title" style="margin: -20px; background: #489; padding: 20px; margin-bottom: 20px; border-bottom: 3px solid #267; color: #EEE; font-size: 30px; text-shadow: 1px 2px #267;">
			Please register the plugin...
		</p>
		<p>Registration is <strong style="font-size: 1.1em;">Free</strong> and only has to be done <strong style="font-size: 1.1em;">once</strong>. If you've register before or don't want to register, just click the "No Thank You!" button and you'll be redirected back to the Dashboard.</p>
		<p>In addition, you'll receive a a detailed tutorial on how to use the plugin and a complimentary subscription to our Email Newsletter which will give you a wealth of tips and advice on Blogging and Wordpress. Of course, you can unsubscribe anytime you want.</p>
		<p><?php omni_spotlighter_registration_form($fname,$name,$email);?></p>
		<p style="background: #F8F8F8; border: 1px dotted #ddd; padding: 10px; border-radius: 5px; margin-top: 20px;"><strong>Disclaimer:</strong> Your contact information will be handled with the strictest of confidence and will never be sold or shared with anyone.</p>
	</div>	
<?php
}

function omni_spotlighter_registration_form($fname,$uname,$uemail,$btn='Register',$hide=0, $activate_again='') {
	$wp_url = get_bloginfo('wpurl');
	$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
	$thankyou_url = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;noheader=true';
	$onlist_url   = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;onlist=1'.'&amp;noheader=true';
	$nothankyou_url   = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;no=1'.'&amp;noheader=true';
	?>
	
	<?php if ( $activate_again != 1 ) { ?>
	<script><!--
	function trim(str){ return str.replace(/(^\s+|\s+$)/g, ''); }
	function imo_validate_form() {
		var name = document.<?php echo $fname;?>.name;
		var email = document.<?php echo $fname;?>.from;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var err = ''
		if ( trim(name.value) == '' )
			err += '- Name Required\n';
		if ( reg.test(email.value) == false )
			err += '- Valid Email Required\n';
		if ( err != '' ) {
			alert(err);
			return false;
		}
		return true;
	}
	//-->
	</script>
	<?php } ?>
	<form name="<?php echo $fname;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($activate_again!=1){;?>onsubmit="return imo_validate_form();"<?php }?> style="text-align:center;" >
		<input type="hidden" name="meta_web_form_id" value="566368644" />
		<input type="hidden" name="listname" value="spotlighter" />  
		<input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
		<input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
		<input type="hidden" name="meta_adtracking" value="spotlighter" />
		<input type="hidden" name="meta_message" value="1">
		<input type="hidden" name="meta_required" value="from,name">
		<input type="hidden" name="meta_forward_vars" value="1">	
		 <?php if ( $activate_again == 1 ) { ?> 	
			 <input type="hidden" name="activate_again" value="1">
		 <?php } ?>		 
		<?php if ( $hide == 1 ) { ?> 
			<input type="hidden" name="name" value="<?php echo $uname;?>">
			<input type="hidden" name="from" value="<?php echo $uemail;?>">
		<?php } else { ?>
			<p>Name: </td><td><input type="text" name="name" value="<?php echo $uname;?>" size="25" maxlength="150" />
			<br />Email: </td><td><input type="text" name="from" value="<?php echo $uemail;?>" size="25" maxlength="150" /></p>
		<?php } ?>
		<input class="button-primary" type="submit" name="activate" value="<?php echo $btn; ?>" style="font-size: 14px !important; padding: 5px 20px;" />
	</form>
    <form name="nothankyou" method="post" action="<?php echo $nothankyou_url;?>" style="text-align:center;">
	    <input class="button" type="submit" name="nothankyou" value="No Thank You!" />
    </form>
	<?php
}

function omni_spotlighter_do_list_form_2($fname,$uname,$uemail) {
	$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to register the plugin.';
	if ( trim($_GET['activate_again']) != '' && $msg != '' ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
	}
	?>
	<div class="register" style="width:50%; margin: 100px auto; border: 1px dotted #bbb; padding: 20px;">
		<p class="box-title" style="margin: -20px; background: #489; padding: 20px; margin-bottom: 20px; border-bottom: 3px solid #267; color: #EEE; font-size: 30px; text-shadow: 1px 2px #267;">Thank you...</p>
		<p>A confirmation email has just been sent to your email @ "<?php echo $uemail;?>". In order to register the plugin, check your email and click on the link in that email.</p>
		<p>Click on the button below to Verify and Activate the plugin.</p>
		<p><?php omni_spotlighter_registration_form($fname.'_0',$uname,$uemail,'Verify and Activate',$hide=1,$activate_again=1);?></p>
		<p>Disclaimer: Your contact information will be handled with the strictest confidence and will never be sold or shared with third parties.</p>
	</div>	
	<?php
}


/* Spotlighter : Shortcode adction */
/* --------------------------- */
/* @author Nimrod Tsabari
 * @since 0.1b
 */
function set_spotlighter($atts,$content=null) {
  extract(shortcode_atts(array(
      'role'			=> '',
      'connector'   	=> 'default',
      'ext'				=> '',
      'background'		=> 'transparent',
      'carpet_color' 	=> 'black',
      'inline'			=> 'no',
	  'pad'				=> 5,
	  'padding'			=> 5,
	  'border_width'	=> 0,
	  'border_color'	=> 'black',
	  'border_style'	=> 'solid',
	  'radius'			=> 0,
	  'carpet_opacity'	=> 70,
	  'text_color'		=> '',
	  'drop_shadow'		=> '0px 0px 0px white'
    ), $atts));

  /* Variables */
  $html = '';
  $role	= strtolower($role);
  $link_connector = 'spotlighter-connector-' . $connector;
  $drop_shadow = trim($drop_shadow);

  if (validate_number($carpet_opacity, 0, 100)) {
  	$carpet_style_class = 'spotlighter-carpetcss-'. $carpet_color .'xx'.$carpet_opacity.'xx'.$text_color.'xx'.$pad;
  } else {
   	$carpet_style_class = 'spotlighter-carpetcss-'. $carpet_color .'xx95xx'.$text_color.'xx'.$pad;
  } 
  
  $pad = ($padding == '5' ? $pad : $padding);
  
  $inline = strtolower(trim($inline));
  
  if ($inline === 'yes') {
  	$inline_class = 'spotlighter-inline ';
  } else {
  	$inline_class = 'spotlighter-block ';
  }
   
  if ($ext != '') {
  	$html .= '<script type="text/javascript"> carpetBox("' . $ext . '", "' . $link_connector . '"); </script>';
  }
  
  if ((in_array($role,array('switch','target'))) && ($content != '')) {
	if ($role === "switch") {
  		$html .= '<script type="text/javascript"> genCarpet(); </script>';
		$html .= '<div class="' . $inline_class . 'spotlighter-switch '. $carpet_style_class . ' ' . $link_connector . '">';
		$html .= do_shortcode($content);
		$html .= '</div> <!-- .spotlighter-switch -->';
	} else {  	
	  $html .= '<div class="spotlighter-box ' . $link_connector . ' spotlighter-temp"></div>';
	  $html .= '<div class="spotlighter-content-container '. $link_connector . ' spotlighter-temp">';
	  $html .= do_shortcode($content);
	  //$html .= '</div> <!-- #spotlighter-content-container -->';
	  $html .= '</div> <!-- #spotlighter-carpet -->';
	  $html .= '<script type="text/javascript"> 
	  	generateBox("' . $link_connector . '", "' . $background . '", ' . $pad . ', ' . $border_width . ', "' . $border_color . '", "' . $border_style . '", ' . $radius . ', "' . $text_color . '", "' . $drop_shadow . '"); 
	  	</script>';
	}
  return $html;
 }
}

function validate_number($variable, $min, $max) {
	$number = intval($variable,10);
	return (($number) && ($number <= $max) && ($number >= $main));
}

add_shortcode( 'spotlighter', 'set_spotlighter' );
add_shortcode( 'spotlight', 'set_spotlighter' );
?>