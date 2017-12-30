<?php


error_reporting(0); // Set E_ALL for debuging
//error_reporting(E_ALL); // Set E_ALL for debuging





// load composer autoload before load elFinder autoload If you need composer
//require './vendor/autoload.php';

// elFinder autoload
require 'elfinder/php/autoload.php';
// ===============================================

// Enable FTP connector netmount
//elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

// // Required for Dropbox network mount
// // Installation by composer
// // `composer require kunalvarma05/dropbox-php-sdk`
// // Enable network mount
// elFinder::$netDrivers['dropbox2'] = 'Dropbox2';
// // Dropbox2 Netmount driver need next two settings. You can get at https://www.dropbox.com/developers/apps
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=dropbox2&host=1"
// define('ELFINDER_DROPBOX_APPKEY',    '');
// define('ELFINDER_DROPBOX_APPSECRET', '');
// ===============================================

// // Required for Google Drive network mount
// // Installation by composer
// // `composer require google/apiclient:^2.0`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'GoogleDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// // Required case of without composer
// define('ELFINDER_GOOGLEDRIVE_GOOGLEAPICLIENT', '/path/to/google-api-php-client/vendor/autoload.php');
// ===============================================

// // Required for Google Drive network mount with Flysystem
// // Installation by composer
// // `composer require nao-pon/flysystem-google-drive:~1.1 nao-pon/elfinder-flysystem-driver-ext`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for One Drive network mount
// //  * cURL PHP extension required
// //  * HTTP server PATH_INFO supports required
// // Enable network mount
// elFinder::$netDrivers['onedrive'] = 'OneDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://dev.onedrive.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL/netmount/onedrive/1"
// define('ELFINDER_ONEDRIVE_CLIENTID',     '');
// define('ELFINDER_ONEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for Box network mount
// //  * cURL PHP extension required
// // Enable network mount
// elFinder::$netDrivers['box'] = 'Box';
// // Box Netmount driver need next two settings. You can get at https://developer.box.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL"
// define('ELFINDER_BOX_CLIENTID',     '');
// define('ELFINDER_BOX_CLIENTSECRET', '');
// ===============================================


// // Zoho Office Editor APIKey
// // https://www.zoho.com/docs/help/office-apis.html
// define('ELFINDER_ZOHO_OFFICE_APIKEY', '');
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string    $attr    attribute name (read|write|locked|hidden)
 * @param  string    $path    absolute file path
 * @param  string    $data    value of volume option `accessControlData`
 * @param  object    $volume  elFinder volume driver object
 * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
 * @param  string    $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	// folder we don't want access too
	
	// hide these
	if ($basename === 'cache') return true;
	if ($basename === 'wp-security-audit-log') return true;
	if ($basename === 'crayon-syntax-highlighter') return true;
	if ($basename === 'wpdm-file-type-icons') return true;
	//if ($basename === 'download-manager-files') return true;
	
	if (strpos($basename,'.php')!==false) return true;


	
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;                                 // else elFinder decide it itself
}



// This is called OUTSIDE of wordpress, so we need to load up wordpress here
// BUT NOW WE ALSO NEED TO FIGURE OUT WHICH MULTIUSER BLOG THE USER WANTS TO ACCCES
// AND *IF* THEY HAVE PERMISSION

require_once("../../../wp-load.php");
$wpuploadinfo = wp_upload_dir(null, false, true);
$wp_upload_url = $wpuploadinfo['baseurl'];
$wp_upload_path = $wpuploadinfo['basedir'];


// ACCESS PERMISSION
if ( !current_user_can('manage_options') ) {
	throw new exception("Access denied to elfinder files");
}

//echo '<div>wp_upload_url = "' . $wp_upload_url . '"  wp_upload_path = "' . $wp_upload_path . '"</div>';

// creat trash folder if not exist?
$trashdir = $wp_upload_path . '/.trash/';
if (!is_dir($trashdir)) {
	mkdir($trashdir);
}














/**
 * Smart logger function
 * Demonstrate how to work with elFinder event api
 *
 * @param  string   $cmd       command name
 * @param  array    $result    command result
 * @param  array    $args      command arguments from client
 * @param  elFinder $elfinder  elFinder instance
 * @return void|true
 * @author Troex Nevelin
 **/
function elfinder_wp_logger($cmd, $result, $args, $elfinder) {
    $log = sprintf('[%s] %s:', date('r'), strtoupper($cmd));
    foreach ($result as $key => $value) {
        if (empty($value)) {
            continue;
        }
        $data = array();
        if (in_array($key, array('error', 'warning'))) {
            array_push($data, implode(' ', $value));
        } else {
            if (is_array($value)) { // changes made to files
                foreach ($value as $file) {
                    $filepath = (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
                    array_push($data, $filepath);
                }
            } else { // other value (ex. header)
                array_push($data, $value);
            }
        }
        $log .= sprintf(' %s(%s)', $key, implode(', ', $data));
    }
    $log .= "\n";

	// wordpress audit log
	do_action('wsal_notice', $log);
}















// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	// 'debug' => true,
	'roots' => array(
		// Items volume
		array(
			'alias' => 'uploads',
			'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
			'path'          => $wp_upload_path,                 // path to files (REQUIRED)
			'URL'           => $wp_upload_url, // URL to files (REQUIRED)
			'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
			'disabled' => array('extract','archive','netmount','empty','mkfile'),
			'uploadAllow'   => array('image', 'text/plain', 'audio', 'video', 'text/xml', 'text/pad',  'application/pad', 'application/pdf', 'application/tgz', 'application/zip', 'application/7z'),// Mimetype `image` and `text/plain` allowed to upload
			'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
			'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
		),

		// Trash volume
		array(
			'id'            => '1',
			'driver'        => 'Trash',
			'path'          => $trashdir,
			'tmbURL'        => $wp_upload_url . '/.trash/.tmb/',
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
			'disabled' => array('extract','archive','netmount','empty','mkfile'),
			'uploadAllow'   => array('image', 'text/plain', 'audio', 'video', 'text/xml', 'text/pad', 'application/pad', 'application/pdf', 'application/tgz', 'application/zip', 'application/7z'),// Mimetype `image` and `text/plain` allowed to upload
			'uploadOrder'   => array('deny', 'allow'),      // Same as above
			'accessControl' => 'access',                    // Same as above
		)

	)


		// commands are https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0
    , 'bind' => array(
        'mkdir mkfile rename duplicate upload rm paste put' => 'elfinder_wp_logger'
    ),

);


// run elFinder
$connector = new elFinderConnector(new elFinder($opts));

//echo '<pre>' . htmlentities(print_r($connector,true)) . '</pre>';
//exit(1);

$connector->run();

