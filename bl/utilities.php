
<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

class RDP_WPB_UTILITIES {
    static function abortExecution(){
        $rv = false;
        $wp_action = self::globalRequest('action');
        if($wp_action == 'heartbeat')$rv = true;
        $isScriptStyleImg = self::isScriptStyleImgRequest();
        if($isScriptStyleImg)$rv = true;           
        return $rv;
    }//abortExecution        
    
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
    
    static function isScriptStyleImgRequest(){
        $url = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : '';        
        $arrExts = self::extensionList();
        $url_parts = parse_url($url);        
        $path = (empty($url_parts["path"]))? '' : $url_parts["path"];
        $urlExt = pathinfo($path, PATHINFO_EXTENSION);
        return key_exists($urlExt, $arrExts);
    }//isScriptStyleImgRequest    
    
    static function extensionList(){
        return array(
                // Image formats
                'jpg'                 		=> 'image/jpeg',
                'jpeg'                 		=> 'image/jpeg',
                'jpe'                           => 'image/jpeg',
                'gif'                          => 'image/gif',
                'png'                          => 'image/png',
                'bmp'                          => 'image/bmp',
                'tif'                     	=> 'image/tiff',
                'tiff'                     	=> 'image/tiff',
                'ico'                          => 'image/x-icon',

                // Video formats
                'asf'                      	=> 'video/x-ms-asf',
                'asx'                      	=> 'video/x-ms-asf',
                'wmv'                          => 'video/x-ms-wmv',
                'wmx'                          => 'video/x-ms-wmx',
                'wm'                           => 'video/x-ms-wm',
                'avi'                          => 'video/avi',
                'divx'                         => 'video/divx',
                'flv'                          => 'video/x-flv',
                'mov'                       	=> 'video/quicktime',
                'qt'                       	=> 'video/quicktime',
                'mpeg'                 		=> 'video/mpeg',
                'mpg'                 		=> 'video/mpeg',
                'mpe'                 		=> 'video/mpeg',
                'mp4'                      	=> 'video/mp4',
                'm4v'                      	=> 'video/mp4',
                'ogv'                          => 'video/ogg',
                'webm'                         => 'video/webm',
                'mkv'                          => 'video/x-matroska',

                // Text formats
                'txt'               		=> 'text/plain',
                'asc'               		=> 'text/plain',
                'c'               		=> 'text/plain',
                'cc'               		=> 'text/plain',
                'h'               		=> 'text/plain',
                'csv'                          => 'text/csv',
                'tsv'                          => 'text/tab-separated-values',
                'ics'                          => 'text/calendar',
                'rtx'                          => 'text/richtext',
                'css'                          => 'text/css',
                'htm'                     	=> 'text/html',
                'html'                     	=> 'text/html',

                // Audio formats
                'mp3'                  		=> 'audio/mpeg',
                'm4a'                  		=> 'audio/mpeg',
                'm4b'                  		=> 'audio/mpeg',
                'ra'                       	=> 'audio/x-realaudio',
                'ram'                       	=> 'audio/x-realaudio',
                'wav'                          => 'audio/wav',
                'ogg'                      	=> 'audio/ogg',
                'oga'                      	=> 'audio/ogg',
                'mid'                     	=> 'audio/midi',
                'midi'                     	=> 'audio/midi',
                'wma'                          => 'audio/x-ms-wma',
                'wax'                          => 'audio/x-ms-wax',
                'mka'                          => 'audio/x-matroska',

                // Misc application formats
                'rtf'                          => 'application/rtf',
                'js'                           => 'application/javascript',
                'pdf'                          => 'application/pdf',
                'swf'                          => 'application/x-shockwave-flash',
                'class'                        => 'application/java',
                'tar'                          => 'application/x-tar',
                'zip'                          => 'application/zip',
                'gz'                      	=> 'application/x-gzip',
                'gzip'                      	=> 'application/x-gzip',
                'rar'                          => 'application/rar',
                '7z'                           => 'application/x-7z-compressed',
                'exe'                          => 'application/x-msdownload',

                // MS Office formats
                'doc'                          => 'application/msword',
                'pot'                  		=> 'application/vnd.ms-powerpoint',
                'pps'                  		=> 'application/vnd.ms-powerpoint',
                'ppt'                  		=> 'application/vnd.ms-powerpoint',
                'wri'                          => 'application/vnd.ms-write',
                'xla'              		=> 'application/vnd.ms-excel',
                'xls'              		=> 'application/vnd.ms-excel',
                'xlt'              		=> 'application/vnd.ms-excel',
                'xlw'              		=> 'application/vnd.ms-excel',
                'mdb'                          => 'application/vnd.ms-access',
                'mpp'                          => 'application/vnd.ms-project',
                'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
                'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
                'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
                'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
                'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
                'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
                'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
                'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
                'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
                'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
                'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
                'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
                'onetoc' 			=> 'application/onenote',
                'onetoc2' 			=> 'application/onenote',
                'onetmp' 			=> 'application/onenote',
                'onepkg' 			=> 'application/onenote',

                // OpenOffice formats
                'odt'                          => 'application/vnd.oasis.opendocument.text',
                'odp'                          => 'application/vnd.oasis.opendocument.presentation',
                'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
                'odg'                          => 'application/vnd.oasis.opendocument.graphics',
                'odc'                          => 'application/vnd.oasis.opendocument.chart',
                'odb'                          => 'application/vnd.oasis.opendocument.database',
                'odf'                          => 'application/vnd.oasis.opendocument.formula',

                // WordPerfect formats
                'wp'                       	=> 'application/wordperfect',
                'wpd'                       	=> 'application/wordperfect',

                // iWork formats
                'key'                          => 'application/vnd.apple.keynote',
                'numbers'                      => 'application/vnd.apple.numbers',
                'pages'                        => 'application/vnd.apple.pages',
        );       
    }//extensionList
    
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
    
    static function entitiesPlain($string){
        return str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&quest;',  '&#39;' ), array ( '&', '"', "'", '<', '>', '?', "'" ), $string ); 
    } //entitiesPlain 
    
    static function unXMLEntities($string) { 
       return str_replace (array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ) , array ( '&', '"', "'", '<', '>' ), $string ); 
    } //unXMLEntities 
    
    static function isValidUrl ($url="") {

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
    } //isValidUrl
    
    public static function rgempty( $name, $array = null ) {
        if ( is_array( $name ) ) {
                return empty( $name );
        }

        if ( ! $array ) {
                $array = $_POST;
        }

        $val = self::rgar( $array, $name );

        return empty( $val );
    }//rgempty
    
    public static function rgget( $name, $array = null ) {
        if ( ! isset( $array ) ) {
                $array = $_GET;
        }

        if ( isset( $array[ $name ] ) ) {
                return $array[ $name ];
        }

        return '';
    }//rgget    

    public static function rgpost( $name, $do_stripslashes = true ) {
        if ( isset( $_POST[ $name ] ) ) {
                return $do_stripslashes ? stripslashes_deep( $_POST[ $name ] ) : $_POST[ $name ];
        }

        return '';
    } //rgpost
    
    public static function rgar( $array, $name ) {
        if ( isset( $array[ $name ] ) ) {
                return $array[ $name ];
        }

        return '';
    }//rgar  
    
    static function GUID(){
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }  //GUID   
        
}//RDP_WPB_UTILITIES
