<?php 
/*
Plugin Name: RDP Linkedin Login
Plugin URI: http://robert-d-payne.com/
Description: Add extensible Linkedin log-in to a WordPress site
Version: 1.7.0
Author: Robert D Payne
Author URI: http://robert-d-payne.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Turn off all error reporting
//error_reporting(E_ALL^ E_WARNING);
global $wpdb;
$dir = plugin_dir_path( __FILE__ );
define('RDP_LL_PLUGIN_BASEDIR', $dir);
define('RDP_LL_PLUGIN_BASEURL',plugins_url( null, __FILE__ ) );
define('RDP_LL_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RDP_LL_SESSION_TABLE', $wpdb->prefix . 'rdp_ll_session');
include_once 'bl/rdpLLUtilities.php' ;
global $sLLAction;
$sLLAction = RDP_LL_Utilities::globalRequest('rdpllaction');

ob_start();

class RDP_LINKEDIN_LOGIN_PLUGIN {
    public static $plugin_slug = 'rdp-linkedin-login'; 
    public static $options_name = 'rdp_linkedin_login_options';    
    public static $version = '1.7.0';    
    private $_options = array();
    
    public function __construct() {
        $options = get_option( RDP_LINKEDIN_LOGIN_PLUGIN::$options_name );
        if(is_array($options))$this->_options = $options;        
        $this->load_dependencies();
    }//__construct

    private function load_dependencies() {
        if (is_admin()){
            include_once 'pl/rdpLLAdminMenu.php' ;
        } 
        
        include_once 'bl/rdpLLDatapass.php';
        require_once 'bl/simple_html_dom.php';
           
        include_once 'pl/rdpLL.php' ;            
    }//load_dependencies  
    
    private function define_front_hooks(){
        if(defined( 'DOING_AJAX' ))return;
        $oLL = new RDP_LINKEDIN_LOGIN(self::$version,$this->_options);
        add_action( 'wp_enqueue_scripts', array($oLL, 'scriptsEnqueue') ); 
        $oLL->run();        
    }//define_front_hooks

    private function define_admin_hooks() {
        if(!is_admin())return;
        if(defined( 'DOING_AJAX' ))return;
        add_action('admin_menu', 'RDP_LL_AdminMenu::add_menu_item');
        add_action('admin_init', 'RDP_LL_AdminMenu::admin_page_init');        
    }//define_admin_hooks
    
    public function run() {
        $this->define_front_hooks();
        $this->define_admin_hooks();
        if(defined( 'DOING_AJAX' ))return;

        $fLLRegisterNewUser = isset($this->_options['fLLRegisterNewUser'])? $this->_options['fLLRegisterNewUser'] : 'off';
        if($fLLRegisterNewUser == 'on'  && RDP_LL_Utilities::$sessionIsValid )add_filter('logout_url', 'RDP_LL_Utilities::logoutURL', 1000, 2);
    }//run  

    public static function install(){
        global $wpdb;
        $wpdb->suppress_errors();
        $wpdb->show_errors(false); 

        $table_name = RDP_LL_SESSION_TABLE;

        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
          $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if ( ! empty( $wpdb->collate ) ) {
          $charset_collate .= " COLLATE {$wpdb->collate}";
        } 
        
        $sql = "CREATE TABLE $table_name (
                session_key varchar(32) NOT NULL,
                session_data longtext NOT NULL,
                date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                date_expired datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (session_key)
                ) $charset_collate;";          
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $result = dbDelta( $sql ); 
    }//install   
    
    public static function cron_exec() {
        RDP_LL_DATAPASS::purge();
    }    
    
    public static function deactivate() {
        $timestamp = wp_next_scheduled( 'rdp_ll_cron_hook' );
        wp_unschedule_event( $timestamp, 'rdp_ll_cron_hook' );
    }//deactivate    
} //RDP_LINKEDIN_LOGIN_PLUGIN

register_activation_hook( __FILE__, array( 'RDP_LINKEDIN_LOGIN_PLUGIN', 'install' ) );
register_deactivation_hook( __FILE__, array( 'RDP_LINKEDIN_LOGIN_PLUGIN', 'deactivate' ) );


function rdp_linkedin_login_run() {
    add_action( 'rdp_ll_cron_hook', array( 'RDP_LINKEDIN_LOGIN_PLUGIN','cron_exec') );
    if ( ! wp_next_scheduled( 'rdp_ll_cron_hook' ) ) {
        wp_schedule_event( time(), 'daily', 'rdp_ll_cron_hook' );
    }     
    
    // prevent running code unnecessarily
    if(RDP_LL_Utilities::abortExecution())return;

    /* handle requests for member count */
    $uri = $_SERVER['REQUEST_URI'];    
    $slug = '/rdpllaction/member_count';
    $pos = strpos($uri, $slug);
    if($pos !== false){
        global $wpdb;
        $nTotal = 0;        
        $sSQL = "Select count(*)num From $wpdb->users;";
        $row = $wpdb->get_row($sSQL); 
        if($wpdb->num_rows){
            $nTotal = $row->num; 
        }   
        $count = number_format($nTotal, 0, '.', ','); 
        echo $count;
        die;
    }
    
    /* handle posts of pushed user data */
    $slug = '/rdpllaction/user_push';
    $pos = strpos($uri, $slug);
    if($pos !== false){
        $data = file_get_contents("php://input");
        $user = json_decode($data);
        if(!is_object($user) || !property_exists($user, 'emailAddress') || empty($user->emailAddress)){
            echo 'Invalid User';
            die;
        }        
        $rv = RDP_LL_Utilities::handleUserRegistration ($user);
        if($rv){
            echo 'User Added';
        }else{
            echo 'User Not Added';
        }
        die;
    }    
    
    $oRDP_LL_PLUGIN = new RDP_LINKEDIN_LOGIN_PLUGIN();     
    if(!wp_script_is('js-cookie')){
        wp_register_script('js-cookie', plugins_url( 'pl/js/js.cookie.js' , __FILE__ ), array( ), '2.0.4', TRUE);
        wp_enqueue_script('js-cookie');
    }
    $slug = '/rdpllaction/authorize';
    $pos = strpos($uri, $slug);    
    global $sLLAction;
    if(strtolower($sLLAction) == 'login' || $pos !== false){
        include_once 'pl/rdpLLLogin.php' ;
        exit();
    } else {
        $oRDP_LL_PLUGIN->run();        
    }
}//rdp_ll_run
add_action('wp_loaded','rdp_linkedin_login_run',1);

/*  EOF  */
