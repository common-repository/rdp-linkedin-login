=== Plugin Name ===
Contributors: rpayne7264
Tags: linkedin,rdp linkedin,rdp ll,rdp linkedin login,linkedin login
Requires at least: 3.0
Tested up to: 5.2
Stable tag: 1.7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add extensible Linkedin login capabilities to a WordPress site.

== Description ==

RDP Linkedin Login provides:

* Login button shortcode - shows a *Sign in with LinkedIn* button when logged out
* Ability to register a visitor with the WordPress installation
* Ability to push newly-registered visitor info to another site that is also using RDP Linkedin Login
* Ability to display member count for a site
* Ability to display the member count from another site that is also using RDP LinkedIn Login
* Logged-in session security using nonces and client IP address
* OOP with hooks and filters for easy integration and customization
* Ability to set an after-login redirect url, by using a custom JavaScript function
* Ability to run custom JavaScript code after login, by defining a rdp_ll_login_onClose function

= Support =

Posting to the WordPress.org Support Forum does not send me notifications of new issues. Therefore, please send support requests using the [contact form on my web site.](http://www.rdptechsolutions.com/contact/)


= Sponsor =

This plug-in brought to you through the generous funding of [Laboratory Informatics Institute, Inc.](http://www.limsinstitute.org/)



== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'RDP Linkedin Login'
3. Click the Install Now link.
3. Activate RDP Linkedin Login from your Plugins page.


= From WordPress.org =

1. Download RDP Linkedin Login zip file.
2. Upload the 'rdp-linkedin-login' directory to your '/wp-content/plug-ins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate RDP Linkedin Login from your Plugins page.


= After Activation - Go to 'Settings > RDP Linkedin Login' and: =

1. Get LinkedIn Application API keys using the link and settings shown at the top of the settings page.
2. Enter API Key.
3. Enter Secret Key.
4. Set other configurations as desired.
5. Click 'Save Changes' button.
6. Add the [rdp-linkedin-login] shortcode to a page, post, or widget.


= Extra =

1. For more control, add a linkedin-login.custom.css file to your theme's folder.



== Frequently Asked Questions ==

= So what does your plugin offer? =

You get to offer users the ability to *Sign in with Linkedin.*  The associated sign-in and registration hooks offer great flexibility for utilizing user data in custom coding situations.

= How do I get support? =

Please send support requests using the [contact form on my web site.](http://www.rdptechsolutions.com/contact/)


== Usage ==

To display a *Sign in with LinkedIn* button, use the **[rdp-linkedin-login]** shortcode in a widget or page.


To display the member count, add the **[rdp-linkedin-login-member-count]** shortcode to a widget.

It is also possible to display the member count from another site that is also using the RDP LinkedIn Login plugin. Simply use the optional *url* attribute, by adding **rdpllaction/member_count** to the target domain.

= example: =
[rdp-linkedin-login-member-count url=http://www.example.com/rdpllaction/member_count]


== Screenshots ==

1. Log-in button in sidebar. 
2. Pop-up actions menu for logged-in user. Additional custom links can be added via the settings page and/or PHP coding.
3. Settings page.


== Change Log ==

= 1.7.0 =
* Modified **RDP_LL_Login** to use the new r_liteprofile app scope
* Removed the ability to add a list of company IDs that a registered user will automatically follow
* Removed the **RDP_LL_Company** class
* Added **RDP_LL_DATAPASS->fillLite()** 
* Modified constructor of **RDP_LL_Login** for multisite compatibility, where redirectURI is set

= 1.6.3 =
* Added **num-connections** to profile parameters
* Updated **RDP_LL_DATAPASS** to utilize num-connections, including numConnections_get() and numConnections_set()
* Removed expired token check in **RDP_LL_DATAPASS::save()** function
* Reverted **RDP_LINKEDIN_LOGIN::handleLogout()** function to delete session data when a user logs out
* Modified constructor of **RDP_LL_Login** for multisite compatibility, where _redirectURI is set
* Modified factory methods of **RDP_LL_DATAPASS**
* Added fontello icons
* Added down pointing caret to indicate action sub-menu when user is logged in

= 1.6.2 =
* Fixed SQL command in **RDP_LL_DATAPASS::purge()** function
* Modified **RDP_LINKEDIN_LOGIN::handleLogout()** function to no longer delete session data when a user logs out

= 1.6.1 =
* Fixed issue with setting timezone for LinkedIn session data
* Fixed issue with oAuth response throwing 404 error
* Fixed issue with attempting to register existing users

= 1.6.0 =
* Refactored login procedure to utilize the LinkedIn Person token ID for associating LinkedIn users with WordPress users
* Updated **RDP_LL_Utilities::handleUserRegistration()** function so it returns either **WP_User->ID** or boolean **false**
* Added **userID** parameter to **RDP_LL_Utilities::handleRegisteredUserSignOn()** function, which comes directly from the **RDP_LL_Utilities::handleUserRegistration()** function
* Modified **rdp_ll_before_registered_user_login** filter to utilize three args: $fLoggedIn , $user ,$userID
* Added arg for **rdp_ll_before_user_login** action hook to include a RDP_LL_DATAPASS object
* Added arg for **rdp_ll_after_user_login** action hook to include JSON object representing a LinkedIn Person
* Changed *Sign In with LinkedIn* button to new style
* Modified **RDP_LL_Utilities::abortExecution()** function
* Modified **RDP_LL_Utilities::isScriptStyleImgRequest()** function
* Added private static **RDP_LL_Utilities::updateUsermeta()** function
* Added **RDP_LINKEDIN_LOGIN_PLUGIN::install()** function
* Added **RDP_LINKEDIN_LOGIN_PLUGIN::deactivate()** function
* Added custom **rdp_ll_session** table
* Added **RDP_LINKEDIN_LOGIN_PLUGIN::cron_exec()** function
* Added WP_Cron job to purge custom session table
* Updated plug-in logo


= 1.5.10 =
* Refactored run() routine to ensure consistent state with WordPress user logged-in status
* Corrected issue with saving Push URL on settings page
* Added empty index.php files to sub-folders to prevent directory browsing

= 1.5.9 =
* Added priority to wp_footer action in function shortcode_login
* Moved the wp_footer action, in function shortcode_login(), outside of conditional logic, to ensure scripts and styles are printed

= 1.5.8 =
* Refactored code to use WordPress logout_url filter

= 1.5.7 =
* Changed priority of wp_loaded hook to 1
* Corrected misspelling of location property in RDP_LL_DATAPASS class

= 1.5.6 =
* Removed use of jQuery Cookie plug-in
* Added JS Cookie JavaScript API to handle browser cookies

= 1.5.5 =
* Added rdp_ll_app_profile_parameters_filter
* Modified close script of the login pop-up window - removal of rdp_ll_login_redirect cookie is no longer default behavior if a rdp_ll_login_onClose function is defined
* Modified **Redirect Code Example**

= 1.5.4 =
* Updated sign-in function to add location to user meta table

= 1.5.3 =
* Added properties to RDP_LL_DATAPASS class to store additional user data (formatted-name, industry, location, positions) 
* Modified RDP_LIG_Login class to utilize the new properties of the RDP_LL_DATAPASS class

= 1.5.2 =
* Modified code that retrieves member count

= 1.5.1 =
* Modified log-in script to work with caching

= 1.5.0 =
* Added ability to push new user registration to a main site
* Updated action rdp_ll_after_insert_user to accept second parameter (JSON object representing a LinkedIn Person)
* Added action rdp_ll_after_user_push
* Updated screenshot #3

= 1.4.2 =
* Added second parameter to call to wp_login action hook
* Added error handling to member count shortcode function
* Changed styling of pop-up actions menu

= 1.4.1 =
* Changed styling of pop-up actions menu

= 1.4.0 =
* Added a member-count shortcode
* Added ability to specify additional custom links in the pop-up actions menu via settings page
* Added rdp_ll_app_scope_filter

= 1.3.0 =
* Added explicit calls to wp_print_scripts() and wp_print_styles() in wp_footer() hook
* Renamed handle of linkedin.common.css 

= 1.2.0 =
* Removed enqueue for URL script 

= 1.1.0 =
* Added Linkedin Common CSS
* Removed Query Object script
* Removed URL script
* Updated close script of the login popup window
* Added ability to run custom JavaScript code after login, by defining a rdp_ll_login_onClose function

= 1.0.0 =
* Initial RC


== Upgrade Notice ==

= 1.7.0 =
* Reworked the plugin to use the r_liteprofile app scope
* Removed the ability to add a list of company IDs that a registered user will automatically follow because the API is no longer available

= 1.6.0 =
* Reworked login procedure to utilize the LinkedIn Person token ID for associating LinkedIn users with WordPress users
* Modified **rdp_ll_before_registered_user_login** filter to utilize three args: $fLoggedIn , $user ,$userID
* Added arg for **rdp_ll_before_user_login** action hook to include a RDP_LL_DATAPASS object
* Added arg for **rdp_ll_after_user_login** action hook to include JSON object representing a LinkedIn Person
* Added custom **rdp_ll_session** table
* Added WP_Cron job to purge custom session table

== Other Notes ==

== External Scripts Included ==
* JS Cookie v2.0.4 under MIT License
* jQuery.PositionCalculator v1.1.2 under MIT License


== Hook Reference: ==

= rdp_ll_before_user_login =

* Param 1: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Param 2: RDP_LL_DATAPASS object
* Fires before attempting to register or log-in a user.


= rdp_ll_after_insert_user =

* Param 1: WP_User object
* Param 2: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Fires after a new user is registered with the site. *(Register New Users? must be enabled)*


= rdp_ll_after_user_push =

* Param 1: WP User Object
* Param 2: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Param 3: Array of results including HTTP headers or WP_Error if the request failed
* Fires after a new user's LinkedIn information is pushed to another site. *(Register New Users? must be enabled)*


= rdp_ll_after_registered_user_login =

* Param: WP User Object
* Fires after a registered user is logged into the site. *(Register New Users? must be enabled)*


= rdp_ll_registered_user_login_fail =

* Param: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Fires after a failed attempt to log registered user into the site. *(Register New Users? must be enabled)*


= rdp_ll_after_user_login =

* Param 1: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Param 2: RDP_LL_DATAPASS object
* Fires after attempting to register or log-in a user.


= rdp_ll_after_scripts_styles =

* Param: none
* Fires after enqueuing plug-in-specific scripts and styles



== Filter Reference: ==

= rdp_ll_render_login =

* Param 1: String containing log-in HTML for the **[rdp-ingroups-login]** shortcode
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: log-in HTML for the **[rdp-ingroups-login]** shortcode


= rdp_ll_before_insert_user =

* Param 1: WP_User->ID or false if the user ID does not exist in the usermeta table, based on search for LinkedIn user token id and meta_key = _rdp_ll_id
* Param 2: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Return: WP_User->ID or false, if the user does not already exist in WordPress


= rdp_ll_before_registered_user_login =

* Param 1: Boolean indicating if user is logged in based on result of WordPress is_user_logged_in() function
* Param 2: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, id, public-profile-url, location, headline and summary
* Param 3: WP_User->ID or false, if the user does not already exist in WordPress
* Return: Boolean indicating if user is logged in


= rdp_ll_custom_menu_items =

* Param 1: Array to hold custom link data
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: Array of links, where the link text is the key and the link URL is the value


= rdp_ll_actions_submenu =

* Param: String containing HTML of popup actions menu, which appears after clicking on the picture or name displayed by the plugin, after a person is logged in
* Return: String containing HTML of actions menu


= rdp_ll_app_scope_filter =

* Param: String containing default LinkedIn application scopes
* Return: String containing LinkedIn application scopes, each separated by a space


= rdp_ll_app_profile_parameters_filter =

* Param: String containing default LinkedIn application profile parameters
* Return: String containing LinkedIn application profile parameters


== Javascript Function Reference: ==

= rdp_ll_login_onClose =

* Param: redirect URL
* Fires upon successful log-in, just before the log-in pop-up window closes.


== Redirect Code Example ==

In this example, all links with class rdp_jb_must_sign_in are assigned an event listener that sets a cookie, with the cookie value derived from the link's href attribute. The cookie name should always be rdp_ll_login_redirect.

When the pop-up log-in window executes its close script, the function rdp_ll_login_onClose is called, and it reloads the page to show that the visitor is logged in. In the document.ready() function, the cookie is read, then deleted, and the parent window is redirected to the appropriate URL.

= Example Code of Custom Sign-In JavaScript File =

`

var $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_sign_in_onLoad);

function rdp_sign_in_onLoad(){
    var redirectPath = Cookies.get('rdp_ll_login_redirect');
    var loggedIn = $j('body').hasClass('logged-in');
    if(loggedIn && redirectPath && redirectPath != 'undefined'){
        Cookies.remove('rdp_ll_login_redirect', { path: '/' });
        window.location.href = redirectPath;
    }

    $j('#rdp-jb-main').on( "click", '.title.rdp_jb_must_sign_in' , function(event){
        event.preventDefault();  
        var redirectURL = $j(this).attr('href');
        var date = new Date();
        date.setTime(date.getTime()+(30*1000));
        Cookies.set('rdp_ll_login_redirect', redirectURL, {expires: date, path: '/' })
    });
}//rdp_sign_in_onLoad

function rdp_ll_login_onClose(redirect_url){
    window.location.reload();
}//rdp_ll_login_onClose


`
