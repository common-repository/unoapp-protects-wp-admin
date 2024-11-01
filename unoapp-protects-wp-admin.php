<?php
/**
  Plugin Name: Unoapp Protects WP-Admin
  Plugin URI: http://www.unoapp.com/wp-plugins/unoapp-protects-wp-admin
  Description: "Unoapp protects wp-admin" is helping for admin advanced security. The plugin provides an option to change wp-admin URL for both sides like logged in and without login and the plugin also restrict admin access by multiple ips based.
  Author: Unoapp
  Author URI: http://www.unoapp.com/
  Version: 1.1
 */
/*
 * Note!: If you lost your ip or insert wrong ip you can update option "unopwa_ips" into database directly.
 * Note!: Once you unchecked enable option, don't forget to update config.php and .htaccess files, it will not works sometimes due to permisstion issues or other security plugins.
 */

if (!defined('ABSPATH'))
    exit; // throw out if tries to accessed directly
/**
 * Initialize plugin admin menu 
 * @create new menu
 * @create plugin settings page
 */
add_action('admin_menu', 'init_unopwa_admin_menu');
if (!function_exists('init_unopwa_admin_menu')):

    function init_unopwa_admin_menu() {

        add_options_page('WP-Admin Advanced Security', 'WP-Admin Advanced Security', 'manage_options', 'unopwa-settings', 'init_unopwa_admin_option_page');
    }

endif;


/** Define Action to register admin Options */
add_action('admin_init', 'init_unopwa_options_fields');
/** Register options */
if (!function_exists('init_unopwa_options_fields')):

    function init_unopwa_options_fields() {
        register_setting('unopwa_setting_options', 'unopwa_active');
        register_setting('unopwa_setting_options', 'unopwa_before_rewrite_text');
        register_setting('unopwa_setting_options', 'unopwa_ips');
    }

endif;

add_action('wp_logout', 'unopwa_auto_redirect_external_after_logout', 1);

/** logout action redirection on updated url */
if (!function_exists('unopwa_auto_redirect_external_after_logout')):

    function unopwa_auto_redirect_external_after_logout() {
        register_setting('unopwa_setting_options', 'unopwa_preview');
        register_setting('unopwa_setting_options', 'unopwa_rewrite_text');
        update_option('unopwa_preview', 1);
        update_option('unopwa_rewrite_text', get_option('unopwa_before_rewrite_text'));
        $getUnopwaOptions = get_unopwa_setting_options();
        wp_redirect(home_url($getUnopwaOptions['unopwa_rewrite_text']));
    }

endif;

/** Add settings link to plugin list page in admin */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'unopwa_action_links');
if (!function_exists('unopwa_action_links')):

    function unopwa_action_links($links) {
        $links[] = '<a href="' . get_admin_url(null, 'options-general.php?page=unopwa-settings') . '">Settings</a> | <a href="http://www.unoapp.com/support/" target="_blank">support</a>';
        return $links;
    }

endif;

/** admin HTML form */
if (!function_exists('init_unopwa_admin_option_page')):

    function init_unopwa_admin_option_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        if (get_option('permalink_structure')) {
            $permalink_structure_val = 'yes';
        } else {
            $permalink_structure_val = 'no';
        }
        ?>
        <div style="width: 80%; padding: 10px; margin: 10px;"> 
            <h1>WP-Admin Advanced Security Settings</h1>
            <!-- Start Options Form -->
            <form name="unopwa_form" action="options.php" method="post" id="unopwa-settings-form-admin" onSubmit="return UnopwaValidateIPs(document.unopwa_form.unopwa_ips)">
                <input type="hidden"  id="check_permalink" value="<?php echo $permalink_structure_val; ?>">	
                <div class="unopwa-setting">
                    <!-- General Setting -->	
                    <div class="first unopwa-tab" id="div-unopwa-general">
                        <p><label>Enable: </label><input type="checkbox" id="unopwa_active" name="unopwa_active" value='1'                          <?php
                            if (get_option('unopwa_active') != '') {
                                echo ' checked="checked"';
                            }
                            ?>/></p>
                        <p id="adminurl"><label>Admin Slug: </label><input type="text" id="unopwa_rewrite_text" name="unopwa_before_rewrite_text" value="<?php echo esc_attr(get_option('unopwa_before_rewrite_text')); ?>"  placeholder="custom-admin" size="30" required="required">(<i>Add New Secure Admin URL Slug ( i.e custom-admin )</i>)</p>
                    </div>
                    <p ><strong style="color:red;" >Important!:</strong> Follow these steps:</p>	

                    <p >1) Save above slug.</p>
                    <span><?php echo get_submit_button('Save Settings', 'button-primary', 'submit', '', ''); ?></span>

                    <p >2) Please put below two lines code in your wp-config.php file above of <b>Absolute path (ABSPATH)</b>.</p>
                    <p><?php echo "define('WP_ADMIN_DIR', '" . esc_attr(get_option('unopwa_before_rewrite_text')) . "'); "; ?></p>
                    <p><?php echo "define('ADMIN_COOKIE_PATH', SITECOOKIEPATH . WP_ADMIN_DIR);"; ?></p>

                    <p >3) Some time .htaccess file not work due to some permission issue or other security plugins. if not works then you have to manually put below lines code in your root .htaccess file after the line "RewriteRule ^index\.php$ - [L]"</p>

                    <p><?php echo "RewriteRule ^" . esc_attr(get_option('unopwa_before_rewrite_text')) . "/(.*) wp-admin/$1?%{QUERY_STRING} [L]"; ?></p>
                    <p><?php echo "RewriteRule ^" . esc_attr(get_option('unopwa_before_rewrite_text')) . "/?$ wp-login.php [L]"; ?></p>
                    <?php
                    $getUnopwaOptions = get_unopwa_setting_options();
                    if ((isset($getUnopwaOptions['unopwa_active']) && '1' == $getUnopwaOptions['unopwa_active']) && (isset($getUnopwaOptions['unopwa_before_rewrite_text']) && $getUnopwaOptions['unopwa_before_rewrite_text'] != '')) {
                        echo "<p> 4) Don't forget to preview new admin url check below.</p>";
                        echo "<p><strong>Your New Admin URL : </strong>" . home_url($getUnopwaOptions['unopwa_before_rewrite_text']) . " | <strong><blink><a href='" . wp_logout_url(home_url($getUnopwaOptions['unopwa_before_rewrite_text'])) . "'>CLICK HERE</a></blink></strong> for preview new admin URL.</p>";
                    }
                    ?>
                </div>

                <div class="first unopwa-tab">
                    <h2>Admin can access for selected IPs only</h2>
                    <p id="adminurl"><label>IP Address: </label><input type="text" id="unopwa_rewrite_text" name="unopwa_ips" value="<?php echo esc_attr(get_option('unopwa_ips')); ?>" placeholder="<?php echo unopwa_get_the_user_ip(); ?>" size="30">(<i>IPs should be ',' separator ( i.e 192.168.1.126,192.168.1.127 )</i>)</p>
                </div>

                <p ><strong style="color:red;" >Note!:</strong> If you lost your ip or insert wrong ip you can update option "unopwa_ips" into database directly.</p>

                <?php if (get_option('unopwa_active') != '') {?><p ><strong style="color:red;" >Note!:</strong> If you want to disable plugin, don't forget to remove code in config.php and .htaccess files, Some time it will not remove automatically due to some permission issues.</p> <?php } ?>
                <span>
                    <?php echo get_submit_button('Save IP Settings', 'button-primary', 'submit', '', ''); ?></span><script>function UnopwaValidateIPs(inputText)
                                {
                                    var ipformat = /^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)(,\n|,?))*$/;
                                    if(inputText.value == ''){
                                        //
                                    }
                                    else if (inputText.value.match(ipformat))
                                    {
                                        var user_ip = '<?php echo unopwa_get_the_user_ip();?>';
                                        var str = inputText.value;
                                        var n = str.search(user_ip);
                                        var str_array = str.split(',');
                                        
                                        if(str_array.indexOf(user_ip)){
                                            alert('Please enter your IP address('+user_ip+')');
                                            return false;
                                        }
                                        return true;
                                    } else
                                    {
                                        alert("You have entered an invalid IP address!");
                                        document.unopwa_form.unopwa_ips.focus();
                                        return false;
                                    }
                                }

                </script>

                <?php settings_fields('unopwa_setting_options'); ?>
            </form>

            <!-- End Options Form -->
        </div>

        <?php
    }

endif;
/** add js into admin footer */
// Add Check if permalinks are set on plugin activation
register_activation_hook(__FILE__, 'is_permalink_activate');
if (!function_exists('is_permalink_activate')):

    function is_permalink_activate() {
        //add notice if user needs to enable permalinks
        if (!get_option('permalink_structure'))
            add_action('admin_notices', 'permalink_structure_admin_notice');
    }

endif;
if (!function_exists('permalink_structure_admin_notice')):

    function permalink_structure_admin_notice() {
        echo '<div id="message" class="error"><p>Please Make sure to enable <a href="options-permalink.php">Permalinks</a>.</p></div>';
    }

endif;
/** register_install_hook */
if (function_exists('register_install_hook')) {
    register_uninstall_hook(__FILE__, 'init_install_unopwa_plugins');
}
//flush the rewrite
if (!function_exists('init_install_unopwa_plugins')):

    function init_install_unopwa_plugins() {
        flush_rewrite_rules();
    }

endif;
/** register_uninstall_hook */
/** Delete exits options during disable the plugin */
if (function_exists('register_uninstall_hook')) {
    register_uninstall_hook(__FILE__, 'flush_rewrite_rules');
    register_uninstall_hook(__FILE__, 'init_uninstall_unopwa_plugins');
}
//Delete all options after uninstall the plugin
if (!function_exists('init_uninstall_unopwa_plugins')):

    function init_uninstall_unopwa_plugins() {
        delete_option('unopwa_active');
        delete_option('unopwa_ips');
        delete_option('unopwa_rewrite_text');
        delete_option('unopwa_before_rewrite_text');
        delete_option('unopwa_preview');
    }

endif;

require dirname(__FILE__) . '/unopwa-class.php';
/** register_deactivation_hook */
/** Delete exits options during deactivation the plugin */
if (function_exists('register_deactivation_hook')) {
    register_deactivation_hook(__FILE__, 'init_deactivation_unopwa_plugins');
}

//Delete all options after uninstall the plugin
if (!function_exists('init_deactivation_unopwa_plugins')):

    function init_deactivation_unopwa_plugins() {
        delete_option('unopwa_active');
        delete_option('unopwa_logout');
        remove_action('init', 'init_unopwa_admin_rewrite_rules');
        flush_rewrite_rules();
    }

endif;
/** register_activation_hook */
/** Delete exits options during disable the plugin */
if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, 'init_activation_unopwa_plugins');
}
//Delete all options after uninstall the plugin
if (!function_exists('init_activation_unopwa_plugins')):

    function init_activation_unopwa_plugins() {
        delete_option('unopwa_logout');
        flush_rewrite_rules();
    }

endif;

add_action('admin_init', 'unopwa_flush_rewrite_rules');
//flush_rewrite_rules after update value
if (!function_exists('unopwa_flush_rewrite_rules')):

    function unopwa_flush_rewrite_rules() {
        if (isset($_POST['option_page']) && $_POST['option_page'] == 'unopwa_setting_options' && $_POST['unopwa_active'] == '') {
            flush_rewrite_rules();
        }
    }

endif;
?>
