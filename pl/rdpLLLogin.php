<?php

class RDP_LL_Login{
    private $_redirectURI = '';
    const _scope = 'r_liteprofile r_emailaddress';
    private $_API_KEY = '';
    private $_API_SECRET = '';
    private $_datapass = null;
    private $_options = false;
    
    function __construct() {
        
        // OAuth 2 Control Flow
        if (isset($_GET['error'])) {
            // LinkedIn returned an error
            print $_GET['error'] . ': ' . $_GET['error_description'];
            exit;
        }         
        
        $nBlogID = get_current_blog_id();

        $this->_redirectURI = esc_url( get_home_url($nBlogID, '/rdpllaction/authorize' ) );
        
        $this->_options = get_option(RDP_LINKEDIN_LOGIN_PLUGIN::$options_name);
        
        if(false === $this->_options){
            $this->renderMissingSettingsMessage();
        }

        $this->_API_KEY = $this->_options['sLLAPIKey'];
        $this->_API_SECRET = $this->_options['sLLAPISecretKey'];
        
        if(!isset($_GET['code']))$this->handleAuthorizationCode($nBlogID);

        $authPass = $this->handleAuthToken();

        if($authPass === false)$this->renderLoginFailMessage('Authpass');

        // Congratulations! You have a valid token. Now fetch profile

        $profileParams = 'projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';
        $profileParams = apply_filters('rdp_ll_app_profile_parameters_filter ', $profileParams);
        $resource = sprintf('/v2/me?%s', $profileParams);
        $response = RDP_LL_Utilities::fetch2($resource,$authPass['access_token']);
        $data = wp_remote_retrieve_body($response);
        $userProfile = json_decode($data);
        
        $resource = '/v2/emailAddress?q=members&projection=(elements*(handle~))';
        $response = RDP_LL_Utilities::fetch2($resource,$authPass['access_token']);
        $data = wp_remote_retrieve_body($response);
        $userEmail = json_decode($data);
        
        // flatten out the salient data
        $userProfile->emailAddress = '';
        $userProfile->localizedFirstName = '';
        $userProfile->localizedLastName = '';
        $userProfile->formattedName = '';
        $userProfile->pictureUrl = '';
        
        // pull email address from $userEmail object
        if(is_object($userEmail) 
                && property_exists($userEmail, 'elements') 
                && !empty($userEmail->elements) 
                && property_exists($userEmail->elements[0]->{'handle~'},'emailAddress')):
            // add email address to $userProfile object
            $userProfile->emailAddress = $userEmail->elements[0]->{'handle~'}->emailAddress;
        else:
            $this->renderLoginFailMessage('Profile'); 
        endif;
        
        // pull first name from $userProfile object
        if(property_exists($userProfile, 'firstName')):
            foreach ($userProfile->firstName->localized as $key => $value) {
                // add localizedFirstName to $userProfile object
                $userProfile->localizedFirstName = $value;
                break;
            }            
        endif;

        // pull last name from $userProfile object
        if(property_exists($userProfile, 'lastName')):
            foreach ($userProfile->lastName->localized as $key => $value) {
                // add localizedLastName to $userProfile object
                $userProfile->localizedLastName = $value;
                break;
            }            
        endif; 
        
        // add formattedName to $userProfile object
        $userProfile->formattedName = trim($userProfile->localizedFirstName . ' ' . $userProfile->localizedLastName);
        
        // pull profile picture from $userProfile object
        if(property_exists($userProfile, 'profilePicture') && !empty($userProfile->profilePicture->{'displayImage~'}->elements)):
            // add pictureUrl to $userProfile object
            $userProfile->pictureUrl = $userProfile->profilePicture->{'displayImage~'}->elements[0]->identifiers[0]->identifier;
        endif;
     


        /* Create and load up a new datapass object */
        $key = $userProfile->id;
        $this->_datapass = RDP_LL_DATAPASS::get($key);
        $this->_datapass->fillLite($userProfile);
        $this->_datapass->expires_in_set($authPass['expires_in']);
        $this->_datapass->expires_at_set($authPass['expires_at']);
        $this->_datapass->access_token_set($authPass['access_token']);
        $this->_datapass->ipAddress_set(RDP_LL_Utilities::getClientIP());
        $this->_datapass->sessionNonce_set('new');
        $this->_datapass->save();        
        
        do_action('rdp_ll_before_user_login', $userProfile, $this->_datapass); 

        $fLLRegisterNewUser = isset($this->_options['fLLRegisterNewUser'])? $this->_options['fLLRegisterNewUser'] : 'off';
        if($fLLRegisterNewUser == 'on'){
            add_action('rdp_ll_after_insert_user', array( &$this, 'afterUserInsert' ),10,2 );
            $userID = RDP_LL_Utilities::handleUserRegistration($userProfile);
            $fLoggedIn = RDP_LL_Utilities::handleRegisteredUserSignOn($userProfile,$userID);
        }

        do_action('rdp_ll_after_user_login', $userProfile, $this->_datapass);

        $this->renderCloseScript();
    }//__construct
    
    public function afterUserInsert($wp_user, $user){
        $fLLRegisterNewUser = isset($this->_options['fLLRegisterNewUser'])? $this->_options['fLLRegisterNewUser'] : 'off';
        $pushURL = (isset($this->_options['sPushURL']))? $this->_options['sPushURL'] : '' ;
        if($pushURL && $fLLRegisterNewUser == 'on'){
            $url = trailingslashit($pushURL) . 'rdpllaction/user_push'; 
            try {
                $response = wp_remote_post( $url, array(
                        'body' => json_encode($user)
                    )
                ); 
                do_action('rdp_ll_after_user_push', $wp_user, $user,  $response);
            } catch (Exception $e) {
                //ignore error
            }
        }
    }//afterUserInsert
    
    private function renderCloseScript(){
        $JS = <<<EOS
<script type='text/javascript'>
    function rdp_ll_login_onReady(){
        var redirectPath = Cookies.get('rdp_ll_login_redirect');
        if(!redirectPath || redirectPath == 'undefined'){
            window.opener.location.reload();
        }else{
            if(typeof(window.opener.rdp_ll_login_onClose) === typeof(Function)){
                window.opener.rdp_ll_login_onClose(redirectPath);    
            }else{
                Cookies.remove('rdp_ll_login_redirect', { path: '/' });
                window.opener.location.href = redirectPath;     
            }                
        }

        window.close();
    }
    jQuery(document).ready(rdp_ll_login_onReady);
</script>  

EOS;
        
        $pre_load_scripts = array('jquery','js-cookie');

        echo '<html><head>';
        foreach ( $pre_load_scripts as $script ) {
                wp_print_scripts( $script );
        }        
        echo $JS;
        echo '</head><body></body></html>';
        exit;        
    }//renderCloseScript
    
    private function renderMissingSettingsMessage(){
        $sMsg = <<<EOD
<p>RDP Linkedin Login settings not found.<br />
Visit 'Settings > RDP Linkedin Login' and:<br />
1. Get a LinkedIn Application API key using the link and settings shown in the white box.<br />
2. Enter API Key.<br />
3. Enter Secret Key.<br />
4. Set other configurations as desired.<br />
5. Click 'Save Changes' button.</p>
EOD;
        
        exit($sMsg);        
    }//handleMissingSettingsMessage
    
    private function renderLoginFailMessage($code){
        exit("Unable to complete login process.<br />Please try again.<br />Code: $code");      
    }//handleLoginFailMessage
    
    private function handleAuthToken(){
        // User authorized your application
        if(!isset($_GET['code']))return false;
        $state = (isset($_GET['state']))?$_GET['state']:'';

        $loginPass = get_transient( $state );
        $authPass = false;
        if (false !== $loginPass ) {
            $authPass = $this->getAccessToken($this->_redirectURI,$_GET['code']);
            if(false !== $authPass){
                $authPass['blog_id'] = $loginPass['blog_id'];
            }
        }

        return $authPass;
    }//handleAuthToken
    
    private function getAccessToken($redirectURI,$code)
    {   
        $params = array('grant_type' => 'authorization_code',
                        'client_id' => $this->_API_KEY,
                        'client_secret' => $this->_API_SECRET,
                        'code' => $code,
                        'redirect_uri' => $redirectURI,
                  );

        // Access Token request
        $url = 'https://www.linkedin.com/oauth/v2/accessToken?' . http_build_query($params);

        // Retrieve access token information
        $response = wp_remote_get( $url );
        $json = wp_remote_retrieve_body( $response );
        // Native PHP object, please
        $token = json_decode($json);
        
        if(!is_object($token)):
            // Try again
            $response = wp_remote_get( $url );
            $json = wp_remote_retrieve_body( $response );
            // Native PHP object, please
            $token = json_decode($json);            
        endif;
        
        $authPass = false;
        if(is_object($token) && property_exists($token, 'access_token') && !empty($token->access_token)){
            // Store access token and expiration time
            $authPass = array(
                'access_token' => $token->access_token,
                'expires_in' => $token->expires_in, // relative time (in seconds)
                'expires_at' => time() + $token->expires_in  // absolute time  
            );
        }

        return $authPass;
    }  //getAccessToken    
    
    private function handleAuthorizationCode($blog_id){
        // Start authorization process
        $scope = apply_filters('rdp_ll_app_scope_filter', self::_scope);
        
        
        $params = array('response_type' => 'code',
                        'client_id' => $this->_API_KEY ,
                        'scope' => $scope,
                        'state' => uniqid('', true), // unique long string
                        'redirect_uri' => $this->_redirectURI,
                  );

        // Authentication request
        $url = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);

        // Needed to identify request when it returns to us
        $loginPass = array(
            'state' => $params['state'],
            'blog_id' => $blog_id
            );

        set_transient( $params['state'], $loginPass, 60 );
        // Redirect user to authenticate
        header("Location: $url");
        exit;
    }//handleAuthorizationCode
    
    
}//RDP_LIG_Login

$oLogin = new RDP_LL_Login();





/* EOF */
