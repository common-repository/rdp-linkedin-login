<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

class RDP_LINKEDIN_LOGIN {
    private $_key = '';
    private $_datapass = null;
    private $_version;
    private $_options = array();
    
    public function __construct($version,$options){
        $this->_version = $version;
        $this->_options = $options;        

        add_shortcode('rdp-linkedin-login', array(&$this, 'shortcode_login'));        
        add_shortcode('rdp-linkedin-login-member-count', array(&$this, 'shortcode_member_count'));        

    }//__construct
    
    function run() {
       
        if ( defined( 'DOING_AJAX' ) ) return;  
        
        // exit if doing normal WP log out
        $sAction = RDP_LL_Utilities::globalRequest('action'); 
        if(strtolower($sAction) === 'logout')return;

        // exit if doing normal WP log in        
        $wpSubmit = RDP_LL_Utilities::globalRequest('wp-submit');
        if(!empty($wpSubmit))return;
        
        $fLLRegisterNewUser = isset($this->_options['fLLRegisterNewUser'])? $this->_options['fLLRegisterNewUser'] : 'off';
       
        if($fLLRegisterNewUser == 'on' && is_user_logged_in()){
            $userID = get_current_user_id();
            $this->_key = get_user_meta($userID, '_rdp_ll_id', true);
        }

        if(!has_filter('widget_text','do_shortcode'))add_filter('widget_text','do_shortcode',11);
        $this->_datapass = RDP_LL_DATAPASS::get($this->_key); 

        if(isset($_GET['rdpllaction']) && $_GET['rdpllaction'] == 'logout'){
            self::handleLogout($this->_datapass);
        }

        if(!$this->_datapass->data_filled()) {
            return;
        }
        if($this->_datapass->tokenExpired()){
            if(is_user_logged_in()) wp_logout();
            return;
        }

        $storedIP = $this->_datapass->ipAddress_get();
        $currentIP = RDP_LL_Utilities::getClientIP();
        $ipVerified = ($storedIP === $currentIP );
        $rdpligrt =  $this->_datapass->sessionNonce_get();
        $rdpligrtAction = 'rdp-ll-read-'.$this->_key; 
        if($rdpligrt === 'new'){
            $rdpligrt = wp_create_nonce( $rdpligrtAction );
            $this->_datapass->sessionNonce_set($rdpligrt);
            $this->_datapass->save();
        }
        $nonceVerified = wp_verify_nonce( $rdpligrt, $rdpligrtAction );
     
        
        if($nonceVerified && $ipVerified ){
            RDP_LL_Utilities::$sessionIsValid = true;
        }else{
            if(is_user_logged_in()) wp_logout();
        }
    }//run
    
    public function shortcode_member_count($atts) {
        $url = (isset($atts['url']))? $atts['url'] : '' ;
        $count = '';
        if($url){
            // Remove all illegal characters from a url
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // Validate url
            if (filter_var($url, FILTER_VALIDATE_URL) === false){
                return $count;
            }
            
            $response = wp_remote_post( $url);            
            if ( !is_wp_error( $response ) ) {
                $code = $response['response']['code'];
                if($code  != 200)return $count;
                $count = wp_remote_retrieve_body($response);                
            }            

        }else{
            global $wpdb;
            $nTotal = 0;        
            $sSQL = "Select count(*)num From wp_users;";
            $row = $wpdb->get_row($sSQL); 
            if($wpdb->num_rows){
                $nTotal = $row->num; 
            }   

            $count = number_format($nTotal, 0, '.', ',');               
        }
        
        return $count;
    }//shortcode_member_count
    
   
    public function shortcode_login(){
        if(isset($_GET['rdpllaction']) && $_GET['rdpllaction'] == 'logout')return;
        $fIsLoggedIn = false;
        $token = !empty($this->_datapass)? $this->_datapass->access_token_get() : '';
        if (RDP_LL_Utilities::$sessionIsValid && !empty($token))$fIsLoggedIn = true;
//var_dump(RDP_LL_Utilities::$sessionIsValid);
        $sStatus = ($fIsLoggedIn)? "true":"false";
//echo '$sStatus = ' . $sStatus;        
        $sHTML = '';

        if($sStatus == 'false'){
            $sHTML .= '<img style="cursor: pointer;" class="btnRDPLLogin" src="' . plugins_url( 'images/js-signin.png' , __FILE__ ) . '" > ';
        }else{
            
            $sHTML .= '<a class="rdp-ll-loginout rdp-ll-item logged-in-' . $sStatus . '" aria-haspopup="true" title="My Account">';
            $sHTML .= '<img alt="" src="' . $this->_datapass->pictureUrl_get() . '" class="avatar avatar-26 photo" height="26" width="26"/>';
            $sFName = $this->_datapass->firstName_get();
            if(!empty($sFName))$sHTML .= "Hello, {$sFName}.";
            $sHTML .= '</a>';

            if($this->_datapass->submenuCode_get() == ''):
                $imgSrc = $this->_datapass->pictureUrl_get();
                $fullName = $this->_datapass->fullName_get();
//                $params['rdpllaction'] = 'logout';
                $url = RDP_LL_Utilities::logoutURL( '','' );
               
                $oCustomMenuItems = array();
                
                $text_string = empty($this->_options['sSubmenuActions'])? '' : $this->_options['sSubmenuActions'];
                if(!empty($text_string)){
                    $str = nl2br($text_string,false);
                    $str = str_replace('<br>', ',', $str);
                    $oLinks = explode(',', $str);
                    foreach($oLinks as $sLink){
                        $linkPieces = explode('|', $sLink);
                        if(count($linkPieces) < 2)continue;
                        $oCustomMenuItems[$linkPieces[0]] = $linkPieces[1];
                    }                    
                }
               
                
                $oCustomMenuItems = apply_filters( 'rdp_ll_custom_menu_items', $oCustomMenuItems, $sStatus );
                $sCustomMenuItems = '';
                foreach ($oCustomMenuItems as $key => $value) {
                    $sCustomMenuItems .= '<p><a href="' . $value . '">' . $key . '</a></p>';
                }
                
                if($sCustomMenuItems){
                    $sCustomMenuItems = sprintf('<div class="rdp_ll_custom_menu_sep"></div><div class="rdp-ll-custom-menu-items">%s</div>',$sCustomMenuItems);
                }

                $submenuHTML = <<<EOD
        <div id="rdp-ll-sub-wrapper" class="hidden">
            <div class="rdp-ll-wrap">
            <p>
                <img alt="" src="{$imgSrc}" class="rdp-ll-avatar rdp-ll-avatar-64 photo" height="64" width="64"/>
                <span class="rdp-ll-display-name">{$fullName}</span>
                <div class="clear">&nbsp;</div>
            </p>
            <p>
                <a href="{$url}">Sign Out</a>
            </p>
            </div><!-- .rdp-ll-wrap -->
                             {$sCustomMenuItems}
        </div><!-- .rdp-ll-sub-wrapper -->   
   
EOD;
                $submenuHTML = apply_filters('rdp_ll_actions_submenu', $submenuHTML);
                $this->_datapass->submenuCode_set($submenuHTML);
                
            endif;
        }
        
        add_action('wp_footer', array(&$this,'renderUserActionsSubmenu'), 9);
        $this->handleScripts($sStatus);
        return apply_filters( 'rdp_ll_render_login', $sHTML, $sStatus );
    }//shortcode_login
    
    public function renderUserActionsSubmenu(){
        remove_action('wp_footer', array(&$this,'renderUserActionsSubmenu'));
        echo $this->_datapass->submenuCode_get();
        wp_print_scripts();
        wp_print_styles();
    }//renderUserActionsSubmenu

   
    public function scriptsEnqueue(){
        // GLOBAL FRONTEND SCRIPTS
        wp_enqueue_script( 'rdp-ll-global', plugins_url( 'js/script.global.js' , __FILE__ ), array( 'jquery','jquery-query' ), $this->_version, TRUE);        
        $params = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'site_url' => get_site_url()
        );      
        wp_localize_script( 'rdp-ll-global', 'rdp_ll_global', $params );   
    }//scriptsEnqueue

    private function handleScripts($status){
        if(wp_style_is( 'rdp-ll-style-common', 'enqueued' )) return;
        // LinkedIn CSS
        wp_register_style( 'rdp-ll-style-common', plugins_url( 'style/linkedin.common.css' , __FILE__ ) );
	wp_enqueue_style( 'rdp-ll-style-common' );
        
        // RDP Linkedin Login CSS
        wp_register_style( 'rdp-ll-style', plugins_url( 'style/default.css' , __FILE__ ),null, $this->_version );
        wp_enqueue_style( 'rdp-ll-style' );        
        
        $filename = get_stylesheet_directory() .  '/linkedin-login.custom.css';
        if (file_exists($filename)) {
            wp_register_style( 'rdp-ll-style-custom',get_stylesheet_directory_uri() . '/linkedin-login.custom.css',array('rdp-ll-style' ) );
            wp_enqueue_style( 'rdp-ll-style-custom' );
        }
        
        // RDP LL login script
        wp_enqueue_script( 'rdp-ll-login', plugins_url( 'js/script.login.js' , __FILE__ ), array( 'jquery','jquery-query','rdp-ll-global' ), $this->_version, TRUE);
        $url = get_home_url();
        $params = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'loginurl' => $url 
        );
        wp_localize_script( 'rdp-ll-login', 'rdp_ll_login', $params );

        // Position Calculator
        if(!wp_script_is('jquery-position-calculator'))wp_enqueue_script( 'jquery-position-calculator', plugins_url( 'js/position-calculator.min.js' , __FILE__ ), array( 'jquery' ), '1.1.2', TRUE);

        do_action( 'rdp_ll_after_scripts_styles');
    }//handleScripts



    public static function handleLogout($datapass = null){
        if($datapass != null && $datapass->data_filled()){
            RDP_LL_DATAPASS::delete($datapass->key());             
        }

        $params = RDP_LL_Utilities::clearQueryParams();
        $url = add_query_arg($params);
        
        // log the user out of WP, as well
        if(is_user_logged_in()){
            $url = wp_logout_url( $url );
        }

        // Hack to deal with 'headers already sent' on Linux servers
        // and persistent browser session cookies
        echo "<meta http-equiv='Refresh' content='0; url={$url}'>";
        ob_flush(); // ob_start() called near top of /rdp-linkedin-login/index.php
        exit;
    }//handleLogout
    
    
}//class RDP_LINKEDIN_LOGIN


/* EOF */
 
