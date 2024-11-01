<?php
/*
 * Unoapp Protects WP-Admin
 * @register_install_hook()
 * @register_uninstall_hook()
 * */
?>
<?php

if (!defined('ABSPATH'))
    exit; // throw out if tries to accessed directly
GLOBAL $getUnopwaOptions;
/** Get all options value */
if (!function_exists('get_unopwa_setting_options')):

    function get_unopwa_setting_options() {
        global $wpdb;
        $unopwaOptions = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'unopwa_%'");

        foreach ($unopwaOptions as $option) {
            $unopwaOptions[$option->option_name] = $option->option_value;
        }
        return $unopwaOptions;
    }

endif;

$getUnopwaOptions = get_unopwa_setting_options();

if (isset($getUnopwaOptions['unopwa_preview']) && '1' == $getUnopwaOptions['unopwa_preview'] && isset($getUnopwaOptions['unopwa_active']) && '1' == $getUnopwaOptions['unopwa_active']) {
    add_filter( 'lostpassword_url',  'unopwa_lostpassword_url', 10, 0);  
    add_filter( 'login_url', 'unopwa_login_url', 10, 3);
    add_filter( 'register_url', 'unopwa_register_page', 10, 3);
    add_action('login_enqueue_scripts', 'unopwa_load_jquery');
    add_action('init', 'init_unopwa_admin_rewrite_rules');
    add_action('init', 'unopwa_admin_url_redirect_conditions');
    add_action('init', 'unopwa_front_secure_admin', 1);
    add_filter('site_url', 'unopwa_wpadmin_filter', 10, 3);
    add_action('login_footer','unopwa_custom_script',5);
}

  /** Create a new rewrite rule for change to wp-admin url */
if (!function_exists('init_unopwa_admin_rewrite_rules')):

    function init_unopwa_admin_rewrite_rules() {
        $getUnopwaOptions = get_unopwa_setting_options();
        if (isset($getUnopwaOptions['unopwa_active']) && '' != $getUnopwaOptions['unopwa_before_rewrite_text']) {
            $newurl = strip_tags($getUnopwaOptions['unopwa_before_rewrite_text']);
            add_rewrite_rule($newurl . '/?$', 'wp-login.php', 'top');
            add_rewrite_rule($newurl . '/(.*)', 'wp-admin/$1?%{QUERY_STRING}', 'top');
        }
    }

endif;
/**
 * Update Login, Register & Forgot password link as per new admin url
 * */
if (!function_exists('unopwa_load_jquery')):

    function unopwa_load_jquery() {
        wp_enqueue_script("jquery");
    }

endif;

if(!function_exists('unopwa_custom_script')):
function unopwa_custom_script()
{	
$getUnopwaOptions=get_unopwa_setting_options();
if(isset($getUnopwaOptions['unopwa_active']) && ''!=$getUnopwaOptions['unopwa_rewrite_text']){

echo '<script>jQuery(document).ready(function(){
	jQuery("#login #login_error a").attr("href","'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'?action=lostpassword').'");
	jQuery("body.login-action-resetpass p.reset-pass a").attr("href","'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'/').'");
	var formId= jQuery("#login form").attr("id");
if(formId=="loginform"){
	jQuery("#"+formId).attr("action","'.home_url($getUnopwaOptions["unopwa_rewrite_text"]).'");
	}else if("lostpasswordform"==formId){
			jQuery("#"+formId).attr("action","'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'?action=lostpassword').'");
			jQuery("#"+formId+" input:hidden[name=redirect_to]").val("'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'/?checkemail=confirm').'");
		}else if("registerform"==formId){
			jQuery("#"+formId).attr("action","'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'?action=register').'");
			}
		else
			{
				//silent
				}
				//alert(jQuery("#nav a").slice(0).attr("href"));
				';
		$currentUrl = unopwa_get_current_page_url($_SERVER);			
          echo 'jQuery("#nav a").each(function(){
            var linkText = jQuery(this).attr("href").match(/[^/]*(?=(\/)?$)/)[0];
            if(linkText=="wp-login.php"){jQuery(this).attr("href","'.home_url($getUnopwaOptions["unopwa_rewrite_text"]).'");}
			else if(linkText=="wp-login.php?action=register"){jQuery(this).attr("href","'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'?action=register').'");}else if(linkText=="wp-login.php?action=lostpassword"){jQuery(this).attr("href","'.home_url($getUnopwaOptions["unopwa_rewrite_text"].'?action=lostpassword').'");}else { 
				//silent
				}	
        });});';
}

}
endif;

/** Lost password url filter */
if (!function_exists('unopwa_lostpassword_url')):
function unopwa_lostpassword_url() {
    return home_url(get_option('unopwa_rewrite_text') . '?action=lostpassword');
}
endif;

/** Lost register url filter */
if (!function_exists('unopwa_register_page')):
function unopwa_register_page( $register_url ) {
    return home_url(get_option('unopwa_rewrite_text') . '?action=register');
}
endif;

/** Lost login url filter */
if (!function_exists('unopwa_login_url')):
function unopwa_login_url( $login_url, $redirect, $force_reauth ) {
    return home_url(get_option('unopwa_rewrite_text'));
}
endif;

/** admin redirection */
if (!function_exists('unopwa_admin_url_redirect_conditions')):

    function unopwa_admin_url_redirect_conditions() {
        $getUnopwaOptions = get_unopwa_setting_options();
        $unopwaActualURLAry = array
        (
            home_url('/wp-login.php'),
            home_url('/wp-login.php/'),
            home_url('/wp-login'),
            home_url('/wp-login/'),
            home_url('/wp-admin'),
            home_url('/wp-admin/'),
        );
        $request_url = unopwa_get_current_page_url($_SERVER);
        $newUrl = explode('?', $request_url);
        
        if (!is_user_logged_in() && in_array($newUrl[0], $unopwaActualURLAry)) {
            
           $requested_uri = $_SERVER["REQUEST_URI"];
           if (DOING_AJAX && $newUrl[0] == home_url('/wp-admin/admin-ajax.php'))
                return true;
            /** is forgot password link */
            if (isset($_GET['login']) && isset($_GET['action']) && $_GET['action'] == 'rp' && $_GET['login'] != '') {
                $username = $_GET['login'];
                if (username_exists($username)) {
                    //silent is golden
                } else {
                    wp_redirect(home_url('/'), 301); //exit;
                }
            } else {
                wp_redirect(home_url('/'), 301); //exit;
            }
            //exit;
        }
    }

endif;

/** Get the current url */
if (!function_exists('unopwa_current_path_protocol')):

    function unopwa_current_path_protocol($s, $use_forwarded_host = false) {
        $unopwahttp = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
        $unopwasprotocal = strtolower($s['SERVER_PROTOCOL']);
        $unopwa_protocol = substr($unopwasprotocal, 0, strpos($unopwasprotocal, '/')) . (($unopwahttp) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$unopwahttp && $port == '80') || ($unopwahttp && $port == '443')) ? '' : ':' . $port;
        $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $unopwa_protocol . '://' . $host;
    }

endif;
if (!function_exists('unopwa_get_current_page_url')):

    function unopwa_get_current_page_url($s, $use_forwarded_host = false) {
        return unopwa_current_path_protocol($s, $use_forwarded_host) . $s['REQUEST_URI'];
    }

endif;

/** wp-admin url filter */
if (!function_exists('unopwa_wpadmin_filter')):

    function unopwa_wpadmin_filter($url, $path, $orig_scheme) {
        $old = array("/(wp-admin)/");
        $admin_dir = WP_ADMIN_DIR;
        $new = array($admin_dir);
        return preg_replace($old, $new, $url, 1);
    }

endif;

/** without logged in front side admin security */
if (!function_exists('unopwa_front_secure_admin')):

    function unopwa_front_secure_admin() {

        $requested_uri = $_SERVER["REQUEST_URI"];
        $whitelisted_ips = unopwa_get_whitelist_apis();
        $client_ip = unopwa_get_the_user_ip();
        
        if (!empty($whitelisted_ips[0])) {

            if (strpos($requested_uri, $getUnopwaOptions['unopwa_rewrite_text']) !== false || !in_array($client_ip, $whitelisted_ips)) {
                status_header(404);
                nocache_headers();
                include( get_query_template('404') );
                die();
            } else {
                add_action('admin_init', 'unopwa_back_secure_admin', 1);
            }
        } else {

            if (strpos($requested_uri, $getUnopwaOptions['unopwa_rewrite_text']) !== false) {
                status_header(404);
                nocache_headers();
                include( get_query_template('404') );
                die();
            } else {
                add_action('admin_init', 'unopwa_back_secure_admin', 1);
            }
        }
    }

endif;

/** logged in back side admin security */
if (!function_exists('unopwa_back_secure_admin')):

    function unopwa_back_secure_admin() {
        $requested_uri = $_SERVER["REQUEST_URI"];
        if (strpos($requested_uri, 'logout') == false && strpos($requested_uri, 'loggedout') == false) {
            unopwa_force_404();
        }
    }

endif;

/** force fully redirect on 404 page for both side front/back */
if (!function_exists('unopwa_force_404')):

    function unopwa_force_404() {
        $requested_uri = $_SERVER["REQUEST_URI"];
        $whitelisted_ips = unopwa_get_whitelist_apis();
        $client_ip = unopwa_get_the_user_ip();
        
        if (!empty($whitelisted_ips[0])) {
        
            if ((strpos($requested_uri, '/wp-login.php') !== false ||
                    strpos($requested_uri, '/wp-admin') !== false) ||
                    !in_array($client_ip, $whitelisted_ips)
            ) {
        
                status_header(404);
                nocache_headers();
                include( get_query_template('404') );
                die();
            }
        } else {
        
            if ((strpos($requested_uri, '/wp-login.php') !== false ||
                    strpos($requested_uri, '/wp-admin') !== false)) {
       
                status_header(404);
                nocache_headers();
                include( get_query_template('404') );
                die();
            }
        }
       
    }

endif;

/** get user ip */
if (!function_exists('unopwa_get_the_user_ip')):

    function unopwa_get_the_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return apply_filters('wpb_get_ip', $ip);
    }

endif;

/** get whitelisted ips */
if (!function_exists('unopwa_get_whitelist_apis')):

    function unopwa_get_whitelist_apis() {
        $string = esc_attr(get_option('unopwa_ips'));
        return explode(',', $string);
    }

endif;
?>