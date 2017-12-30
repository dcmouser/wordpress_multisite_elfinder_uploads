<?php
/*
Plugin Name: elfinderuploads
Description: Plugin to allow mutli-user blogs to have access to upload folder
Author: mouser@donationcoder.com
Version: 1.0
*/
add_action('admin_menu', 'elfinderuploads_setup_menu');


// for custom commands see: 
//https://stackoverflow.com/questions/16604842/adding-a-custom-context-menu-item-to-elfinder/
 
function elfinderuploads_setup_menu(){
        add_menu_page( 'elFinder Uploads Plugin - Browser', 'elFinder', 'manage_options', 'elfinder-uploads/elfinder.php', 'elfinderuploads_browser', 'dashicons-category', 6 );
}
 
function elfinderuploads_browser(){

// ACCESS PERMISSION
if ( !current_user_can('manage_options') ) {
	throw new exception("Access denied to elfinder plugin browser");
}


// for testing logger
//do_action('wsal_notice', 'elfinder is loading notice');
//do_action('wsal_warning', 'elfinder is loading warning');
//do_action('wsal_critical', 'elfinder is loading critical');


				$dir = plugin_dir_path( __FILE__ );
				$contenturl_elfinder = content_url() . '/plugins/elfinder-uploads';
				$elfinderbaseurl = $contenturl_elfinder . '/elfinder';
				//$mainjsurl = $contenturl_elfinder . '/mainel.js';

				$requirebaseurl = $contenturl_elfinder; // $elfinderbaseurl;
								

				//include_once($dir . 'elfinderscript.html');


		//<script data-main=\"" . $mainjsurl . "\" src=\"//cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.min.js\"></script>
		
		// old connector:
		// 					//url : '" . $elfinderbaseurl . '/' . "php/connector.minimal.php'; // connector URL (REQUIRED)
				
echo "
		<!-- Require JS (REQUIRED) -->
		<!-- Rename 'main.default.js' to 'main.js' and edit it if you need configure elFInder options or any things -->
		<script src=\"//cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.min.js\"></script>

		<script>
  		require.config({
    		baseUrl: \"" . $requirebaseurl . "\",
		  });
		  require( ['mainel']);
		</script>

		<script>
			define('elFinderConfig', {
				// elFinder options (REQUIRED)
				// Documentation for client options:
				// https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
				defaultOpts : {
					url : '" . $contenturl_elfinder . '/' . "wp-connector.php' // connector URL (REQUIRED)
					//url : '" . $elfinderbaseurl . '/' . "php/connector.minimal.php' // connector URL (REQUIRED)
					//url : '" . $elfinderbaseurl . '/' . "php/wp-connector.php' // connector URL (REQUIRED)
					, height: 600
					,commandsOptions : {
						edit : {
							extraOptions : {
								// set API key to enable Creative Cloud image editor
								// see https://console.adobe.io/
								creativeCloudApiKey : '',
								// browsing manager URL for CKEditor, TinyMCE
								// uses self location with the empty value
								managerUrl : ''
							}
						}
						,quicklook : {
							// to enable preview with Google Docs Viewer
							googleDocsMimes : ['application/pdf', 'image/tiff', 'application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
						}
					}
					// bootCalback calls at before elFinder boot up 
					,bootCallback : function(fm, extraObj) {
						/* any bind functions etc. */
						fm.bind('init', function() {
							// any your code
						});
						// for example set document.title dynamically.
						var title = document.title;
						fm.bind('open', function() {
							var path = '',
								cwd  = fm.cwd();
							if (cwd) {
								path = fm.path(cwd.hash) || null;
							}
							document.title = path? path + ':' + title : title;
						}).bind('destroy', function() {
							document.title = title;
						});
					}
				},
				managers : {
					// 'DOM Element ID': { /* elFinder options of this DOM Element */ }
					'elfinder': {}
				}
			});
		</script>
";



				//
        echo '<h1>ElFinder Uploads - Browser</h1>';

				$wpuploadinfo = wp_upload_dir(null, false, true);
				$wp_upload_url = $wpuploadinfo['baseurl'];
				$wp_upload_path = $wpuploadinfo['basedir'];
        echo '<div>Public URLs to these files are relative to "' . $wp_upload_url . '".  You can see the url to any given file by right-clicking, choosing "Get Info", and then copying the Link field.</div><br/>';
        echo '<div id="elfinder"></div>';
}


function elfinderupload_options(){
        echo "<h1>ElFinder Uploads - Options</h1>";
        echo "<div>No options yet.</div>";
}
 
?>