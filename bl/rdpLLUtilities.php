<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

class RDP_LL_Utilities {
    public static $sessionIsValid = false;
    
    static function abortExecution(){
        $wp_action = self::globalRequest('action');
        $rv = ($wp_action == 'heartbeat')? true : false;
        if(!$rv):
            $url = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : '';
            $rv =  self::isScriptStyleImgRequest($url);                  
        endif;
        return $rv;
    }//abortExecution
    
    static function isScriptStyleImgRequest($url){
        if(empty($url))return false;
        $arrExts = self::extensionList();
        $url_parts = parse_url($url);        
        $path = (empty($url_parts["path"]))? '' : $url_parts["path"];
        $urlExt = pathinfo($path, PATHINFO_EXTENSION);
        return key_exists($urlExt, $arrExts);
    }//isScriptStyleImgRequest 
    
    static function extensionList(){
        $ext = array();
        $mimes = wp_get_mime_types();

        foreach ($mimes as $key => $value) {
            $ak = explode('|', $key);
            $ext = array_merge($ext,$ak)  ;      
        }            
        
        return $ext;
    }//extensionList   
    
    public static function globalRequest( $name, $default = '' ) {
        $RV = '';
        $array = $_GET;

        if ( isset( $array[ $name ] ) ) {
                $RV = $array[ $name ];
        }else{
            $array = $_POST;
            if ( isset( $array[ $name ] ) ) {
                    $RV = $array[ $name ];
            }                
        }
        
        if(empty($RV) && !empty($default)) return $default;
        return $RV;
    }     
    
    static function getClientIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        
        if($ipaddress === '::1')$ipaddress = '127.0.0.1';        
        return $ipaddress;
    }

    static function fetch($method, $resource, $access_token,$inputs = null)
    {
        $params = array('oauth2_access_token' => $access_token,
                        'format' => 'json',
                  );
        
        if(is_array($inputs))$params = array_merge($params, $inputs);

        // Need to use HTTPS
        $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);

       
        $response = wp_remote_get( $url, $args );
        return $response;
    }  //fetch
    
    static function fetch2($resource, $access_token,$inputs = null)
    {
        $args = array(
            'headers'     => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ); 

        // Need to use HTTPS
        $url = 'https://api.linkedin.com' . $resource;

        $response = wp_remote_get( $url, $args);
        return $response;
    }  //fetch2
    
    static function renderTokenExpiredMessage(){
        $msg = esc_html__('Your LinkedIn session has expired. Please sign out, and then sign in again with LinkedIn, to view the content.', 'rdp-linkedin-login');
        $sHTML = sprintf('<div id="comment-response-container" class="alert">%s</div>', $msg);
        
        return $sHTML;
    }//renderTokenExpiredMessage
    
    static function handleUserRegistration($user){
        $userdata = WP_User::get_data_by( 'login', $user->emailAddress );
        if(!$userdata)$userdata = WP_User::get_data_by( 'email', $user->emailAddress );
        $userID = NULL;
        if($userdata)$userID = $userdata->ID;
        $userID = apply_filters( 'rdp_ll_before_insert_user', $userID, $user );
        
        
        $userdata = array(
            'user_login'            => $user->emailAddress,
            'nickname'              => $user->emailAddress,
            'first_name'            => $user->localizedFirstName,
            'last_name'             => $user->localizedLastName,
            'user_email'            => $user->emailAddress,         
            'display_name'          => $user->formattedName
            );         
        
        if (is_numeric($userID)){
            $userdata['ID'] = $userID;
            $user_id = wp_update_user( $userdata );
            self::updateUsermeta($user,$userID); 
            return $userID;
        }

        $userdata['show_admin_bar_front'] = 'false';
        $userdata['user_pass']  = wp_generate_password( $length=8, $include_standard_special_chars=false );
        $userID = wp_insert_user($userdata);

        if( is_wp_error($userID) ) return false;
        
        // on success
        self::updateUsermeta($user,$userID);           

        if( is_multisite() ){
            global $blog_id; 
            if ( !is_user_member_of_blog( $userID, $blog_id ) ){
                add_user_to_blog($blog_id, $userID, get_option('default_role'));
            }
        }
        $wp_user = get_user_by( 'id', $userID );
        do_action('rdp_ll_after_insert_user', $wp_user, $user);
      
        return $userID;
    }//handle_user_registration
    
    private static function updateUsermeta($user,$userID) {
        if(!add_user_meta($userID, '_rdp_ll_id', $user->id, true)){
            update_user_meta($userID, '_rdp_ll_id', $user->id);
        }           
        if(property_exists($user, 'publicProfileUrl') && !empty($user->publicProfileUrl)){
            if(!add_user_meta($userID, 'rdp_ll_public_profile_url', $user->publicProfileUrl, true)){
                update_user_meta($userID, 'rdp_ll_public_profile_url', $user->publicProfileUrl);
            } 
        }
        if(property_exists($user, 'pictureUrl') && !empty($user->pictureUrl)){
            if(!add_user_meta($userID, 'rdp_ll_picture_url', $user->pictureUrl, true)){
                update_user_meta($userID, 'rdp_ll_picture_url', $user->pictureUrl);
            } 
        }
        if(property_exists($user, 'headline') && !empty($user->headline)){
            if(!add_user_meta($userID, 'rdp_ll_headline', $user->headline, true)){
                update_user_meta($userID, 'rdp_ll_headline', $user->headline);
            } 
        }            
        if(property_exists($user, 'location') && !empty($user->location->name)){
            if(!add_user_meta($userID, 'rdp_ll_location', $user->location->name, true)){
                update_user_meta($userID, 'rdp_ll_location', $user->location->name);
            } 
        }          
    }//updateUsermete
    
    static function handleRegisteredUserSignOn($user,$userID){
        $fLoggedIn = is_user_logged_in();
        $fLoggedIn = apply_filters( 'rdp_ll_before_registered_user_login', $fLoggedIn,$user,$userID );
        if ($fLoggedIn) return;
        
        $wp_user = new WP_User($userID);
        if( isset( $wp_user->user_login, $wp_user )) {
            clean_user_cache($wp_user->ID);
            wp_clear_auth_cookie();            
            wp_set_current_user( $wp_user->ID, $wp_user->user_login );
            wp_set_auth_cookie($wp_user->ID, false);
            do_action( 'wp_login', $wp_user->user_login, $wp_user);
            do_action('rdp_ll_after_registered_user_login', $wp_user);  
        }else do_action('rdp_ll_registered_user_login_fail',$user);

        return is_user_logged_in();
    }//handleRegisteredUserSignOn

    static function logoutURL($logout_url, $redirect ) {
        $params['rdpllaction'] = 'logout';
        $url = add_query_arg($params);
        return $url;        
    }//logoutURL
     
    static function clearQueryParams(){
        $arr_params = array();
        foreach($_GET as $query_string_variable => $value) {
            if(substr($query_string_variable, 0, 5) == 'rdpll')$arr_params[$query_string_variable] = false;
            if( $query_string_variable == 'wikiembed-override-url')$arr_params[$query_string_variable] = false;
            if( $query_string_variable == 'rdp_we_resource')$arr_params[$query_string_variable] = false;
         }
         return $arr_params;
    }//clearQueryParams 
    
    static function is_valid_url ($url="") {

            if ($url=="") {
                $url=$this->url;
            }

            $url = @parse_url($url);

            if ( ! $url) {


                return false;
            }

            $url = array_map('trim', $url);
            $url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
            $path = (isset($url['path'])) ? $url['path'] : '';

            if ($path == '') {
                $path = '/';
            }

            $path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';



            if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) ) {
                if ( PHP_VERSION >= 5 ) {
                    $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
                }
                else {
                    $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

                    if ( ! $fp ) {
                        return false;
                    }
                    fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                    $headers = fread ( $fp, 128 );
                    fclose ( $fp );
                }
                $headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
                return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
            }

            return false;
        }
        
    static function unparse_url($parsed_url) { 
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
        $pass     = ($user || $pass) ? "$pass@" : ''; 
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
        return "$scheme$user$pass$host$port$path$query$fragment"; 
    } //unparse_url            

}//RDP_LL_Utilities

/* EOF */
