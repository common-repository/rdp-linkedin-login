<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

/**
 * Class for holding LinkedIn session data of a site user
 *
 * @author Robert Payne
 */
class RDP_LL_DATAPASS {
    private $_access_token = null;
    private $_expires_at = 0;
    private $_expires_in = 0; 
    private $_firstName = '';
    private $_lastName = '';
    private $_formattedName = '';
    private $_emailAddress = '';
    private $_pictureUrl = '';
    private $_personID = '';
    private $_headline = '';
    private $_industry = '';
    private $_location = '';
    private $_positions = '';
    private $_publicProfileUrl = '';
    private $_summary = '';
    private $_numConnections = 0;
    private $_data_filled = false;
    private $_data_saved = false;
    private $_key = '';
    private $_ipAddress = 0;
    private $_wp_post_id = 0;
    private $_rdpligrt = '';
    private $_submenu_code = '';
    private $_mars = null;
    
    private function __construct($key = '',$props = null) {
        if(empty($key))return ;
        $this->_key = $key;
        $sTZ = get_option('timezone_string');
        $sTZ = (empty($sTZ))? 'America/New_York' : $sTZ ;
        $this->_mars = new DateTimeZone($sTZ);        
        if(!$props)return ;
        $oProps = get_object_vars($this);
        foreach ($oProps as $key => $value ) {
            $newvalue = (isset($props[$key])) ? $props[$key] : null;
            if ($newvalue === "true") $newvalue = true;
            if ($newvalue === "false") $newvalue = false;
            $this->$key = $newvalue;
        }

        $this->_data_filled = true;
    }//__construct    
    
    
    public static function get($key) {
        global $wpdb;
        $table = RDP_LL_SESSION_TABLE;
        $sSQL = sprintf('Select session_data From %s Where session_key = "%s";',  $table, $key);
        $data = null;
        $row = $wpdb->get_row($sSQL);       
        if($row){
            $data = unserialize($row->session_data);
        }        
        return new self($key,$data); 
    } //get  
    
//    public static function get_new($key) {
//        return new self($key);        
//    }//get_new
    
    public static function delete($key) {
        global $wpdb;
        $table = RDP_LL_SESSION_TABLE;
        $wpdb->delete( $table, array( 'session_key' => $key ), array( '%s' ) );
    }
    
    public static function purge() {
        global $wpdb;
        $table = RDP_LL_SESSION_TABLE;
        $sSQL = "Delete FROM {$table} Where date_expired < now();";
        $wpdb->query($sSQL);
    }//purge


    /**
     * 
     * @param object $user JSON object representing a LinkedIn Person
     * @return void
     */
    public function fill($user) {
        if(empty($user) || !is_object($user)){
            $this->_data_filled = false;
            return;
        }
        
        if(property_exists($user, 'firstName')) $this->_firstName = $user->firstName;
        if(property_exists($user, 'lastName')) $this->_lastName= $user->lastName;
        if(property_exists($user, 'formattedName')) $this->_formattedName = $user->formattedName;
        if(property_exists($user, 'emailAddress')) $this->_emailAddress = $user->emailAddress;
        if(property_exists($user, 'pictureUrl')) $this->_pictureUrl = $user->pictureUrl;
        if(property_exists($user, 'publicProfileUrl')) $this->_publicProfileUrl = $user->publicProfileUrl;
        if(property_exists($user, 'id')) $this->_personID = $user->id;
        if(property_exists($user, 'headline')) $this->_headline = $user->headline;
        if(property_exists($user, 'industry')) $this->_industry = $user->industry;
        if(property_exists($user, 'location')) $this->_location = $user->location;
        if(property_exists($user, 'positions')) $this->_positions = $user->positions;
        if(property_exists($user, 'summary')) $this->_summary = $user->summary;
        if(property_exists($user, 'numConnections')) $this->_numConnections = $user->numConnections;
        
        $this->_data_filled = true;
        $this->_data_saved = false;
        
    }//fill
    
    
    public function fillLite($userProfile) {
        if(empty($userProfile) || !is_object($userProfile)){
            $this->_data_filled = false;
            return;
        } 
        
        if(property_exists($userProfile, 'firstName')) $this->_firstName = $userProfile->localizedFirstName;
        if(property_exists($userProfile, 'lastName')) $this->_lastName= $userProfile->localizedLastName;
        if(property_exists($userProfile, 'formattedName')) $this->_formattedName = $userProfile->formattedName;
        if(property_exists($userProfile, 'emailAddress')) $this->_emailAddress = $userProfile->emailAddress;
        if(property_exists($userProfile, 'pictureUrl')) $this->_pictureUrl = $userProfile->pictureUrl;               

        $this->_data_filled = true;
        $this->_data_saved = false;        
    }//fillLite
    
    public function tokenExpired(){
        $expires = (!empty($this->_expires_at))? $this->_expires_at : time()-5 ;
        $tokenExpired = (time() < $expires)? false : true;
        return $tokenExpired;
    }
    
    public function save() {
        $this->_data_saved = false;

        global $wpdb;
        $wpdb->suppress_errors();
        $wpdb->show_errors(false);         
        $table = RDP_LL_SESSION_TABLE;
        $dateExpiredGMT = gmdate("Y-m-d H:i:s", $this->expires_at_get());
        $recordDate = new DateTime($dateExpiredGMT);  
        $recordDate->setTimezone($this->_mars);  
        $dateExpired = $recordDate->format('Y-m-d H:i:s');        
        
        $sessionData = serialize(get_object_vars($this));
        $wpdb->update( 
                $table, 
                array( 
                        'session_data' => $sessionData,
                        'date_expired' => $dateExpired
                ), 
                array( 'session_key' => $this->_key ), 
                array( 
                        '%s',	
                        '%s',	
                        '%s'	
                ), 
                array( '%s' ) 
        );
        if($wpdb->rows_affected == 0):
            $wpdb->insert( 
                    $table, 
                    array( 
                            'session_key' => $this->_key, 
                            'session_data' => $sessionData,
                            'date_created' => current_time( 'mysql' ),
                            'date_expired' => $dateExpired                    
                    ), 
                    array( 
                            '%s',
                            '%s',
                            '%s',
                            '%s' 
                    ) 
            );             
        endif;
        
        $this->_data_saved = boolval($wpdb->rows_affected);
        
    }//save     
    
    public function key(){
        $token = (isset($this->_key))? $this->_key : '';
        return $token;
    }
    
    public function sessionNonce_get(){
        $token = (isset($this->_rdpligrt))? $this->_rdpligrt : '';
        return $token;
    }
    
    public function sessionNonce_set($value){
        $this->_rdpligrt = $value;
    }
    
    public function data_filled(){
        $filled = (isset($this->_data_filled))? $this->_data_filled : false;
        return $filled;        
    }
    
    public function data_saved(){
        $saved = (isset($this->_data_saved))? $this->_data_saved : false;
        return $saved;        
    }  
    
    public function access_token_get(){
        $token = (isset($this->_access_token))? $this->_access_token : '';
        return $token;
    }
    
    public function access_token_set($value){
        $this->_access_token = $value;
    }

    public function expires_at_get(){
        $expires = (isset($this->_expires_at))? $this->_expires_at : time()-5 ;
        return $expires;
    }
    
    public function expires_at_set($value){
        $this->_expires_at = $value;
    }  
    
    public function expires_in_get(){
        $expires = (isset($this->_expires_in))? $this->_expires_in : 0 ;
        return $expires;
    }
    
    public function expires_in_set($value){
        $this->_expires_in = $value;
    }     
    
    public function fullName_get(){
        $name = (isset($this->_formattedName))? $this->_formattedName : '';
        return $name;
    }
    
    public function fullName_set($value){
        $this->_formattedName = $value;
    }    

    public function firstName_get(){
        $name = (isset($this->_firstName))? $this->_firstName : '';
        return $name;
    }

    public function firstName_set($value){
        $this->_firstName = $value;
    }    
    
    public function lastName_get(){
        $name = (isset($this->_lastName))? $this->_lastName : '';
        return $name;
    }
    
    public function lastName_set($value){
        $this->_lastName = $value;
    }    
    
    public function emailAddress_get(){
        $email = (isset($this->_emailAddress))? $this->_emailAddress : '';
        return $email;
    }
    
    public function emailAddress_set($value){
        $this->_emailAddress = $value;
    } 
    
    public function headline_get(){
        $headline = (isset($this->_headline))? $this->_headline : '';
        return $headline;
    }
    
    public function headline_set($value){
        $this->_headline = $value;
    }    
    
    public function industry_get(){
        $industry = (isset($this->_industry))? $this->_industry : '';
        return $industry;
    }
    
    public function industry_set($value){
        $this->_industry = $value;
    }    
    
    public function location_get(){
        $location = (isset($this->_location))? $this->_location : '';
        return $location;
    }
    
    public function location_set($value){
        $this->_location = $value;
    }  
    
    public function numConnections_get(){
        $cnt = (isset($this->_numConnections))? $this->_numConnections : 0;
        return $cnt;
    }
    
    public function numConnections_set($value){
        $this->_numConnections = $value;
    }     
    
    public function pictureUrl_get(){
        $url = (isset($this->_pictureUrl))? $this->_pictureUrl : '';
        return $url;
    }
    
    public function pictureUrl_set($value){
        $this->_pictureUrl = $value;
    } 
    
    public function positions_get(){
        $positions = (isset($this->_positions))? $this->_positions : '';
        return $positions;
    }
    
    public function positions_set($value){
        $this->_positions = $value;
    }    
    
    public function publicProfileUrl_get(){
        $url = (isset($this->_publicProfileUrl))? $this->_publicProfileUrl : '';
        return $url;        
    }

    public function publicProfileUrl_set($value){
        $this->_publicProfileUrl = $value;
    }

    public function personID_get(){
        $id = (isset($this->_personID))? $this->_personID : '';
        return $id;
    }
    
    public function personID_set($value){
        $this->_personID = $value;
    }
    
    public function summary_get(){
        $summary = (isset($this->_summary))? $this->_summary : '';
        return $summary;
    }
    
    public function summary_set($value){
        $this->_summary = $value;
    }    
    
    public function ipAddress_get(){
        $id = (isset($this->_ipAddress))? $this->_ipAddress : '';
        return $id;
    }
    
    public function ipAddress_set($value){
        $this->_ipAddress = $value;
    }
    
    public function wpPostID_set($value){
        if(!is_numeric($value)) $value = 0;
        $this->_wp_post_id = $value;
    }
    
    public function wpPostID_get(){
        $id = (isset($this->_wp_post_id))? $this->_wp_post_id : 0;
        return $id;
    }
    
    public function submenuCode_set($value){
        $this->_submenu_code = $value;
    }
    
    public function submenuCode_get(){
        $html = (isset($this->_submenu_code))? $this->_submenu_code : '';
        return $html;
    }
    
}//RDP_LL_DATAPASS

/* EOF */
