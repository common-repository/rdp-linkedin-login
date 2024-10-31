<?php 

if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); 

class RDP_LL_AdminMenu {
    /*------------------------------------------------------------------------------
    Add admin menu
    ------------------------------------------------------------------------------*/
    static function add_menu_item()
    {
        if ( !current_user_can('activate_plugins') ) return;
        add_options_page( 'RDP Linkedin Login', 'RDP Linkedin Login', 'manage_options', 'rdp-linkedin-login', 'RDP_LL_AdminMenu::generate_page' );

    } //add_menu_item


    /*------------------------------------------------------------------------------
    Render settings page
    ------------------------------------------------------------------------------*/
    static function generate_page()
    {  
	echo '<div class="wrap">';
        echo '<h2>RDP Linkedin Login</h2>';

        echo '<form action="options.php" method="post">';
        settings_fields('rdp_linkedin_login_options');
        do_settings_sections('rdp-linkedin-login'); 
        echo '<input name="Submit" type="submit" value="Save Changes" />';
        echo '</form>';
        
        echo '<h3 style="margin-top: 40px;"  class="title">';
        esc_html_e("Usage",'rdp-linkedin-login');
        echo '</h3>';
        echo '<p>';
        $text_string = __("To display a %s button, add the <b>[rdp-linkedin-login]</b> shortcode to a widget.",'rdp-linkedin-login');
        $sButtonURL = plugins_url('images/js-signin.png',__FILE__);
        $imgHTML = sprintf('<img style="margin-bottom: -3px;" src="%s" />',$sButtonURL);
        printf($text_string,$imgHTML);
        echo '</p>';
        
        echo '<p>';
        _e("To display the member count, add the <b>[rdp-linkedin-login-member-count]</b> shortcode to a widget.",'rdp-linkedin-login');
        echo '<br>';
        _e("It is also possible display the member count from another site that is also using the RDP LinkedIn Login plugin. Simply use the optional <i>url</i> attribute, by adding <b>rdpllaction/member_count</b> to the target domain.",'rdp-linkedin-login');
        echo '<br><i>';
        _e("example",'rdp-linkedin-login');
        echo ': [rdp-linkedin-login-member-count url=http://www.example.com/rdpllaction/member_count]';
        echo '</i></p>';        
        
        
        echo '<h3 style="margin-top: 40px;"  class="title">';
        esc_html_e("CSS Styling",'rdp-linkedin-login');
        echo '</h3>';
        echo '<p>';
        _e("For more styling control, add a linkedin-login.custom.css file to the active theme folder.",'rdp-linkedin-login');
        echo '</p>';        
        
        
        echo '<h3 style="margin-top: 40px;" class="title">';
        esc_html_e("Hook Reference",'rdp-linkedin-login');
        echo '</h3>';
        echo '</p>';        
        echo '<p><b>rdp_ll_before_user_login</b><br />';
        _e("Param 1: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Param 2: RDP_LL_DATAPASS object<br />Fires before any user is logged into the site via LinkedIn.",'rdp-linkedin-login');
        echo '</p>';  
        
        $sRegNew = __('<i>Register New Users?</i> must be enabled', 'settings page', 'rdp-linkedin-login');
        
        echo '<p><b>rdp_ll_after_insert_user</b><br />';
        _e("Param 1: WP User Object<br />Param 2: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Fires after a new user is registered with the site. ({$sRegNew})",'rdp-linkedin-login');
        echo '</p>';
        
        echo '<p><b>rdp_ll_after_user_push</b><br />';
        _e("Param 1: WP User Object<br />Param 2: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Param 3: Array of results including HTTP headers or WP_Error if the request failed<br />Fires after a new user's LinkedIn information is pushed to another site. ({$sRegNew})",'rdp-linkedin-login');
        echo '</p>';        
        
        echo '<p><b>rdp_ll_after_registered_user_login</b><br />';
        _e("Param: WP User Object<br />Fires after a registered user is logged into the site. ({$sRegNew})",'rdp-linkedin-login');
        echo '</p>';
        echo '<p><b>rdp_ll_registered_user_login_fail</b><br />';
        _e("Param: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Fires after a failed attempt to log registered user into the site. ({$sRegNew})",'rdp-linkedin-login');
        echo '</p>';        
        echo '<p><b>rdp_ll_after_user_login</b><br />';
        _e("Param 1: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Param 2: RDP_LL_DATAPASS object<br />Fires after any user is logged into the site via LinkedIn.",'rdp-linkedin-login');
        echo '</p>';        
        echo '<p><b>rdp_ll_after_scripts_styles</b><br>';
        _e("Param: None<br />Fires after enqueuing plug-in-specific scripts and styles.",'rdp-linkedin-login');
        echo '</p>';

        echo '<h3 style="margin-top: 40px;" class="title">';
        esc_html_e("Filter Reference",'rdp-linkedin-login');
        echo '</h3>';
        echo '<p><b>rdp_ll_render_login</b><br />';
        _e("Param 1: String containing log-in HTML for the [rdp-linkedin-login] shortcode<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Return: log-in HTML for the [rdp-linkedin-login] shortcode",'rdp-linkedin-login');
        echo '</p>';
        echo '<p><b>rdp_ll_before_insert_user</b><br />';
        _e("Param 1: WP_User->ID or false if the user ID does not exist in the usermeta table, based on search for LinkedIn user token id and meta_key = _rdp_ll_id<br />Param 2: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Return: WP_User->ID or false, if the user does not already exist in WordPress",'rdp-linkedin-login');
        echo '</p>';
        echo '<p><b>rdp_ll_before_registered_user_login</b><br />';
        _e("Param 1: Boolean indicating if user is logged in based on result of Wordpress is_user_logged_in() function<br />Param 2: Object representing a LinkedIn Person containing firstName, lastName, formattedName, emailAddress, and pictureUrl<br />Param 3: WP_User->ID or false, if the user does not already exist in WordPress<br />Return: Boolean indicating if user is logged in",'rdp-linkedin-login');
        echo '</p>';
        echo '<p><b>rdp_ll_actions_submenu</b><br />';
        _e("Param: String containing HTML of popup actions menu, which appears after clicking on the picture or name displayed by the plugin after a person is logged in<br />Return: String containing HTML of popup actions menu",'rdp-linkedin-login');
        echo '</p>';
        echo '<p><b>rdp_ll_app_scope_filter</b><br />';
        _e("Param: String containing default LinkedIn application scopes<br />Return: String containing LinkedIn application scopes, each separated by a space",'rdp-linkedin-login');
        echo '</p>';
        echo '<p><b>rdp_ll_app_profile_parameters_filter</b><br />';
        _e("Param: String containing default LinkedIn application profile parameters<br />Return: String containing LinkedIn application profile parameters",'rdp-linkedin-login');
        echo '</p>';
        
        echo '</div>';

    }//generate_page

    static function admin_page_init(){
        if ( !current_user_can('activate_plugins') ) return;
        //Add settings link to plugins page
        add_filter('plugin_action_links', array('RDP_LL_AdminMenu', 'add_settings_link'), 10, 2);

        register_setting(
            'rdp_linkedin_login_options',
            'rdp_linkedin_login_options',
            'RDP_LL_AdminMenu::options_validate'
        );

        // LinkedIn API Section
        add_settings_section(
            'rdp_ll_main',
            esc_html__('LinkedIn API Settings','rdp-linkedin-login'),
            'RDP_LL_AdminMenu::api_section_text',
            'rdp-linkedin-login'
	);
        add_settings_field(
            'sLLAPIKey',
            esc_html__('Client ID:','rdp-linkedin-login'),
            array('RDP_LL_AdminMenu', 'API_Key_Input'),
            'rdp-linkedin-login',
            'rdp_ll_main'
        );
        add_settings_field(
            'sLLAPISecretKey',
            esc_html__('Client Secret:','rdp-linkedin-login'),
            array('RDP_LL_AdminMenu', 'API_Secret_Key_Input'),
            'rdp-linkedin-login',
            'rdp_ll_main'
        );
  
        

        // Linkedin Login Settings
	add_settings_section(
            'rdp_ll_settings',
            esc_html__('Linkedin Login Settings','rdp-linkedin-login'),
            'RDP_LL_AdminMenu::section_text',
            'rdp-linkedin-login'
	);
        
        add_settings_field(
            'fLLRegisterNewUser',
            esc_html__('Register New Users?:','rdp-linkedin-login'),
            array('RDP_LL_AdminMenu', 'Register_New_Users_Input'),
            'rdp-linkedin-login',
            'rdp_ll_settings'
        );
        
        add_settings_field(
            'sSubmenuActions',
            esc_html__('Custom Menu Links:','rdp-linkedin-login'),
            array('RDP_LL_AdminMenu', 'Custom_Submenu_Items_Input'),
            'rdp-linkedin-login',
            'rdp_ll_settings'
        );
        
        add_settings_field(
            'sPushURL',
            esc_html__( 'Push URL:','rdp-linkedin-login'),
            array('RDP_LL_AdminMenu', 'Push_URL'),
            'rdp-linkedin-login',
            'rdp_ll_settings'
        );         

    } //admin_page_init
    
    
    static function Push_URL(){
        $options = get_option( 'rdp_linkedin_login_options' );
        $text_string = empty($options['sPushURL'])? '' : $options['sPushURL']; 
        $text_string = esc_attr($text_string);
        if (filter_var($text_string, FILTER_VALIDATE_URL) === false)$text_string = '';
        echo "<input id='txtPushURL' name='rdp_linkedin_login_options[sPushURL]' class='regular-text code' type='text' value='$text_string' />";
        echo '<p>- ';
        _e("URL of master site to which to push new user information.", 'rdp-linkedin-login');
        echo '<br />- ';
        _ex('<i>Register New Users</i>? must be enabled', 'settings page', 'rdp-linkedin-login');
        echo '</p>';        
    }    
    
    
    static function Custom_Submenu_Items_Input(){
        $options = get_option( 'rdp_linkedin_login_options' );
        $text_string = empty($options['sSubmenuActions'])? '' : $options['sSubmenuActions'];
        $text_string = esc_textarea($text_string); 
        echo '<textarea name="rdp_linkedin_login_options[sSubmenuActions]"  rows="10" cols="50">' . $text_string . '</textarea>';
        echo '<p>- ';
        _e("Custom links to add to the popup actions menu, which appears after clicking on the picture or name displayed by the plugin after a person is logged in.", 'rdp-linkedin-login');
        echo '<br />- ';
        _ex('Separate link text and URLs with a pipe symbol. Separate links with new lines.', 'settings page', 'rdp-linkedin-login');
        echo '</p>';
    }//Companies_To_Follow    
    
   
    static function Register_New_Users_Input() {
         $options = get_option( 'rdp_linkedin_login_options' );
         $fLLRegisterNewUser = empty($options['fLLRegisterNewUser'])? 'on' : $options['fLLRegisterNewUser'];
        echo "<input id='fLLRegisterNewUser' name='rdp_linkedin_login_options[fLLRegisterNewUser]' type='checkbox' " . checked($fLLRegisterNewUser, 'on',false) . " />";
        echo '<p>- ';
        esc_html_e("Register new users with this site after they log in via LinkedIn.", 'rdp-linkedin-login');
        echo '</p>';
     }

    static function API_Key_Input(){
        $options = get_option( 'rdp_linkedin_login_options' );
        $text_string = empty($options['sLLAPIKey'])? '' : $options['sLLAPIKey'];
        $text_string = esc_attr($text_string);

        echo "<input id='txtLLAPIKey' name='rdp_linkedin_login_options[sLLAPIKey]' type='text' value='$text_string' />";
    }

    static function API_Secret_Key_Input(){
        $options = get_option( 'rdp_linkedin_login_options' );
        $text_string = empty($options['sLLAPISecretKey'])? '' : $options['sLLAPISecretKey'];
        $text_string = esc_attr($text_string);

        echo "<input id='txtLLAPISecretKey' name='rdp_linkedin_login_options[sLLAPISecretKey]' type='text' value='$text_string' />";

    }


    /*------------------------------------------------------------------------------
    Validate incoming data
    ------------------------------------------------------------------------------*/
   static function options_validate($input) {
        if(isset($input['sPushURL'])){
            $input['sPushURL'] = trailingslashit($input['sPushURL']);
            if (!preg_match("/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $input['sPushURL'])) {
                $input['sPushURL'] = '';
            }            
        }
        return $input;
    } //options_validate

    static function api_section_text() {
        $sRedirectURL = get_home_url(null,'/rdpllaction/authorize');
        $sLogoURL = RDP_LL_PLUGIN_BASEURL . '/logo.png';
        echo '<div style="border-left: 4px solid #7ad03a;padding: 1px 12px;background-color: #fff;">';
        esc_html_e('Get a LinkedIn Application API key here', 'rdp-linkedin-login');
	echo ': <a href="https://www.linkedin.com/secure/developer" target="_new">https://www.linkedin.com/secure/developer</a>';
        echo '<br /><b>';
	esc_html_e('Application Name', 'rdp-linkedin-login');
	echo ':</b> RDP LinkedIn Login';
        echo '<br /><b>';
	esc_html_e('Application Logo (save to your machine and then upload to LinkedIn)', 'rdp-linkedin-login');
        echo ':</b> ' . sprintf('<a href="%1$s">%1$s</a>',$sLogoURL); 
        echo '<br /><b>';        
	esc_html_e('OAuth 2.0 Redirect URL (add to Auth page)', 'rdp-linkedin-login');
        echo ':</b> ' . $sRedirectURL;
        echo '</div>';
    }

    static function section_text() {
        echo '';
    }
    

    /**
     * Add Settings link to plugins page
     */
    static function add_settings_link($links, $file) {
        if ($file == RDP_LL_PLUGIN_BASENAME){
        $settings_link = '<a href="options-general.php?page=' . RDP_LINKEDIN_LOGIN_PLUGIN::$plugin_slug . '">'.esc_html__("Settings", 'rdp-linkedin-login').'</a>';
         array_unshift($links, $settings_link);
        }
        return $links;
     }

}//RDP_LL_AdminMenu



/* EOF */
