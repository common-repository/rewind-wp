<?php
	/**
	 * rwp_cron_rewind
	 *
	 * Cron job that initiates wp-cron
	 *
	 * @since 1.0.0
	 * @param 
	 */

add_action( 'init', 'rwp_cron_rewind' );
function rwp_cron_rewind() {
	//cron rewind must actually be a wp-cron job request
    if ( isset( $_GET[ 'doing_wp_cron' ] ) && $_SERVER['REQUEST_URI'] == '/wp-cron.php?doing_wp_cron' ) {
	//rewind now
	rwp_Functions::rwp_rewind_now();
	//send data about wordpress timestamp for next rewind to Rewind WP
	rwp_Functions::rwp_set_nextrewind(1);
    }
}

	/**
	 * rwp_add_refresh
	 *
	 * Adds a refresher to the HTML head in the admin Rewind WP settings screen to refresh the page when the duration is on
	 * and refreshes every minute to show the update timestamp
	 *
	 * @since 1.0.0
	 * @param 
	 */

add_filter('admin_head', 'rwp_add_refresh');
function rwp_add_refresh() {
	//	if on the rewind wp settings screen
	if(preg_match('/rewindwp/i', rwp_Functions::rwp_currenturl())) { 
		//	get status
   		$status = rwp_Functions::rwp_get_status();
		//	get next rewind
   		$nextrewind = rwp_Functions::rwp_get_nextrewind();
		// 	make sure Rewind WP is validated and the status is active
  		if(!empty(rwp_Functions::rwp_full_validation_apikey()) && !empty($status)) {
			// 	grab the duration
     			$duration = rwp_Functions::rwp_get_duration();
			//	if duration is set to something then...
			if($duration > 0) { 
				//	add refresh code to refresh every minute to show the latest timer count
				if(!empty($nextrewind)) { echo '<meta http-equiv="refresh" content="60">'; }
			}
		}
	}
}



class rwp_Functions {


	/**
	 * rwp_check_wpcli
	 *
	 * Checks for WP-Cli
	 * 	if the plugin is not working, for debugging purposes: 
	 * 	comment out the bottom return to offically check to see if wp-cli is being detected
	 *	some servers are returning a false positive even though this function simply just shows a warning and
	 *	does not disable anything or stop anything from being used, so it always assumes wp-cli is installed
	 *
	 * @since 1.0.0
	 * @param 
	 */


public static function rwp_check_wpcli() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once 'wp-cli.php';
		return true;
	}
	return true; 
}


	/**
	 * rwp_check_zip
	 *
	 * Checks for zip extension
	 *
	 * @since 1.0.0
	 * @param 
	 */


public static function rwp_check_zip() {
    if ( extension_loaded('zip')) {
	return true;
    }  else { 
        return false;
    }
}


	/**
	 * rwp_api_key
	 *
	 * Function to return apikey from the setting in WordPress 
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_api_key() { 
   $apikey = sanitize_text_field(RWP::getOption( 'api_key' ));
   return(!empty($apikey) ? $apikey : NULL);
}


	/**
	 * rwp_full_validation_apikey 
	 *
	 * Make sure API key is not empty and that it is actually valid through RWP
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_full_validation_apikey() { 

       // 	check to see what the actual key is in the input field
      $rwp_key = self::rwp_api_key();

       //	if empty return nothing
      if(empty($rwp_key)) { return NULL; }

       // 	checking to see what the actual key is
      $validRWP = self::rwp_api_call('apikey', NULL); 

      //	API key is invalid
      if(preg_match('/invalid/i', $validRWP)) { return NULL; }

      // if the two keys are not equal, return empty
      if(strcmp($validRWP, $rwp_key) !== 0) { return NULL; }

      //otherwise we have a match
      return $rwp_key;

}


	/**
	 * rwp_validate_subscription 
	 *
	 * Validates the subscription by verifying it with HQ
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_validate_subscription( $apikey ) {

      // checking for a subscription ~ free or pro
      return self::rwp_api_call('subscription', NULL);
}


	/**
	 * rwp_connect
	 *
	 * Connecto to Rewind WP service
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_connect( ) {
      return self::rwp_api_call('connect', NULL);
}


	/**
	 * rwp_disconnect
	 *
	 * Disconnect from Rewin WP service
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_disconnect( ) {
      return self::rwp_api_call('disconnect', NULL);
}





	/**
	 * rwp_sub_pro 
	 *
	 * Checks subscription and returns true if pro
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_sub_pro( $apikey ) {
      // checking for a subscription ~ free or pro
      if(preg_match('/pro/i', self::rwp_validate_subscription($apikey))) { return true; };
      return false;

}


/**
	 * rwp_set_duration
	 *
	 * Function to set duration to Rewind WP
	 *
	 * @since 1.0.0
	 * @param $delay
	 */
public static function rwp_set_duration($delay) { 
    if(!empty($delay)) { self::rwp_api_call('setduration', $delay); }
}

/**
	 * rwp_get_duration
	 *
	 * Function to get duration from Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_get_duration() { 
    return self::rwp_api_call('getduration', NULL); 
}

/**
	 * rwp_get_lastrewind
	 *
	 * Function to get Last Rewind timestamp from Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_get_lastrewind() { 
    return self::rwp_api_call('getlastrewind', NULL); 
}


/**
	 * rwp_get_lastsnap
	 *
	 * Function to get Last Snapshot timestamp from Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_get_lastsnap() { 
    return self::rwp_api_call('getlastsnap', NULL); 
}



/**
	 * rwp_backup_status
	 *
	 * Function to get backup status for WP Config from Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_backup_status() { 
    return  self::rwp_api_call('getbackup', NULL); 
}

/**
	 * rwp_set_backup
	 *
	 * Function to set backup status and send to Rewind WP
	 *
	 * @since 1.0.0
	 * @param $active [ 0 = false, 1 = true ]
	 */
public static function rwp_set_backup($active) { 
    if(!empty($active)) { return self::rwp_api_call('setbackup', $active); }
}


/**
	 * rwp_rewind_status
	 *
	 * Function to get rewind status from Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_rewind_status() { 
    return  self::rwp_api_call('getrewind', NULL); 
}

/**
	 * rwp_set_rewind
	 *
	 * Function to set rewind status to Rewind WP
	 *
	 * @since 1.0.0
	 * @param $active [ 0 = false, 1 = true ]
	 */
public static function rwp_set_rewind($active) { 
    if(!empty($active)) { return self::rwp_api_call('setrewind', $active); }
}






/**
	 * rwp_get_status
	 *
	 * Function to return status from setting in Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */
public static function rwp_get_status() { 
    return self::rwp_api_call('status', NULL); 
}

	/**
	 * rwp_clear_cron
	 *
	 * Stop cron job
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_clear_cron() {
	self::rwp_set_nextrewind(0);
}


	/**
	 * rwp_set_nextrewind
	 *
	 * Updates NextRewind on Rewind WP server
	 *
	 * @since 1.0.0
	 * @param true = current time | false = NULL
	 */

public static function rwp_set_nextrewind($rewind) { 
    if($rewind) {
	    self::rwp_api_call('setnextrewind', current_time( 'mysql' ));
    } else {
	    self::rwp_api_call('setnextrewind', NULL);
    } 
}

	/**
	 * rwp_get_nextrewind
	 *
	 * Get NextRewind on Rewind WP server
	 *
	 * @since 1.0.0
	 * @param
	 */

public static function rwp_get_nextrewind() { 
    return self::rwp_api_call('getnextrewind', NULL); 
}


	/**
	 * rwp_last_rewind
	 *
	 * Updates Rewind WP server with latest data
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_last_rewind() { 
    self::rwp_api_call('setlastrewind', current_time( 'mysql' )); 
}

	/**
	 * rwp_set_snapshot
	 *
	 * Updates Rewind WP server with latest data
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_last_snapshot() { 
    self::rwp_api_call('setlastsnap', current_time( 'mysql' )); 
}


	/**
	 * rwp_upload_file
	 *
	 * Upload file to WP Rewind server
	 *
	 * @since 1.0.0
	 * @param $folder
	 */

public static function rwp_upload_file($file) { 
    if(!empty($file)) { self::rwp_api_call('upload', $file); }
}


	/**
	 * rwp_delete_file
	 *
	 * Delete file from Rewind WP server
	 *
	 * @since 1.0.0
	 * @param (array) $file
	 */

public static function rwp_delete_file($file) { 
    if(!empty($file)) { self::rwp_api_call('delete', basename($file)); }
}

	/**
	 * rwp_seo_file
	 *
	 * Convert filename into a SEO-url friendly version from Rewind WP
	 *
	 * @since 1.0.0
	 * @param (array) $file
	 */

public static function rwp_seo_file($file) { 
    if(!empty($file)) { return self::rwp_api_call('formatseo', $file); }
}

	/**
	 * rwp_download_file
	 *
	 * Download file from Rewind WP server
	 * Uses native WordPress wp_upload_bits function
	 *
	 * @since 1.0.0
	 * @param (array) $file
	 */

public static function rwp_download_file($file) { 
	//pass something in
    if(!empty($file)) { 
		// get the seo format
        $rwp_seo_file = self::rwp_seo_file($file);
        //activate download class
        if (!class_exists('WP_Http'))
                include_once( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = $http->request($rwp_seo_file);
        if ($response['response']['code'] != 200) { return false; }
        $upload = wp_upload_bits(basename($rwp_seo_file), null, $response['body']);
        if (!empty($upload['error'])) { return false; }
        $uploadfile = $upload['file'];
        $newfile = ABSPATH.basename($rwp_seo_file);
        rename($uploadfile, $newfile);
        return $newfile;
    } else { 
        return NULL;
    } 
}

	/**
	 * rwp_create_snapshot
	 *
	 * Create a snapshot of the wordpress config file, database, wp-content folder, or entire site
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_create_snapshot(){

   //no need being here if the status is inactive
   $status = self::rwp_get_status();

   if(empty($status)) { return false; }

 	//grab wp config if pro
   if(self::rwp_sub_pro(self::rwp_api_key())) { 
	   //if website protection is on... backup the wp-content folder
  	 if(self::rwp_rewind_status() == 1) { self::rwp_wpcontent_backup(); }
   }

   //extract & save database to Rewind WP
   $savedata = self::rwp_saveDatabase(DB_NAME);

   //send data of wordpress timestamp to Rewind WP of the last snapshot
   self::rwp_last_snapshot();

}

	/*
	 * rwp_wpcontent_backup
	 *
	 * WP Content backup
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_wpcontent_backup() { 

   //no need being here if the status is inactive
   $status = self::rwp_get_status();
   if(empty($status < 1)) { return false; }

    //	if not pro then get out
    if(!self::rwp_sub_pro(self::rwp_api_key())) { return false; }

    //	set filenames for variables
    $savefile = ABSPATH.RWP_CONTENT_DIR;
    $zipfile = ABSPATH.RWP_CONTENT_DIR.'.zip';
    $wpcfile = RWP_CONTENT_DIR.'.zip';
    $fullfile = RWP_SITE_URL.'/'.RWP_CONTENT_DIR.'.zip';

    if(file_exists($zipfile)) { unlink($zipfile); }    //	delete the zip file if it exists
    self::rwp_zipFile($savefile, $zipfile, '/'); //	zip it
    if(file_exists($savefile)) { 
	self::rwp_delete_file($wpcfile); //	delete the wp-content file if it exists
	self::rwp_upload_file($fullfile); //	upload the wp-content file if it exists
	if(file_exists($zipfile)) { unlink($zipfile); } //	delete newly created zip file 
    } else {
	echo 'Error saving wp-content.';
    }
}


	/**
	 * rwp_wpconfig_backup
	 *
	 * Backup WP Config by sending the data to WP Rewind to hold for emergencies
	 * 
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_wpconfig_backup() { 
   global $wpdb;

   //	no need being here if the status is inactive
   $status = self::rwp_get_status();
   if(empty($status)) { return false; }

  //	check to see if wpconfig protection
  $wpconfig = self::rwp_backup_status();

 //	set an endpoint
  $endpoint = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action=wpconfig';

  //	if wpconfig protect is on....
  if($wpconfig > 0) { 

	//	grabbing the table prefix
	$table_prefix = $wpdb->prefix;

	//	stuff everything into a json array
  	$wpdata = [
    		'DBUser' => DB_USER,
    		'DBPass' => DB_PASSWORD,
    		'DBHost' => DB_HOST,
    		'DBName' => DB_NAME,
    		'DBChar' => DB_CHARSET,
    		'DBColl' => DB_COLLATE, 
    		'DBTbl' => $table_prefix
	];

  } else {	//	if its off, then we send empty data

  	$wpdata = [
    		'DBUser' => NULL,
    		'DBPass' => NULL,
    		'DBHost' => NULL,
    		'DBName' => NULL,
    		'DBChar' => NULL,
    		'DBColl' => NULL, 
    		'DBTbl' => NULL
	];

  }


	//	wordpress voodoo encoding magic
    $body = wp_json_encode ( $wpdata ) ;

	//	send json post request safely to Rewind WP server
    $response = wp_safe_remote_post( $endpoint, array(
        'method' => 'POST',
        'timeout' => 30,
        'redirection' => 5,
        'blocking' => true,
        'headers' => array(),
        'body' => array ( "wpdata" => $body ),
        'cookies' => array()
        )
    );

	//	if there happens to be an error, something is seriously wrong and just display the message
	//	this should probably never happen but if it does... send us the error message
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
       echo $error_message;
       return false;
    }

    return true;
}

	/**
	 * rwp_wpconfig_restore
	 *
	 * Retrive WP Config data from Rewind WP 
	 * 
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_wpconfig_restore() { 
  global $wpdb;

   //	no need being here if the status is inactive
  $status = self::rwp_status();
  if(empty($status)) { return false; }

   //	check to see if rewind wp already has wp-config data
  $wpconfig_data = self::rwp_api_call("wpconfig", NULL);

  //	set an endpoint
  $endpoint = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action=wpconfig';

  //	get json safely with wordpress voodoo
  $getjson = wp_safe_remote_get(esc_url_raw($endpoint));

  //	use wordpress native feature to retrive json body
  $body = wp_remote_retrieve_body( $getjson );

  //	use wordpress native feature to decode json
  $jsondata = json_decode( $body );

  //	assign variable data from json
  if(!empty($jsondata)) {
	  $dbname = $jsondata->output->DB_NAME;
 	  $dbhost = $jsondata->output->DB_HOST;
  	  $dbuser = $jsondata->output->DB_USER;
  	  $dbpass = $jsondata->output->DB_PASS;
  	  $dbtbl = $jsondata->output->DB_TBL;
  	  $dbchar = $jsondata->output->DB_CHAR;
  	  $dbcoll = $jsondata->output->DB_COLL;
  }

  //	using the built-in wp-cli functionality to re-build the wp-config file
  system("wp config set --quiet DB_NAME $dbname");
  system("wp config set --quiet DB_USER $dbuser");
  system("wp config set --quiet DB_PASSWORD $dbpass");
  system("wp config set --quiet DB_HOST $dbhost");
  system("wp config set --quiet --type=variable table_prefix $dbtbl");
  system("wp config set --quiet DB_CHARSET $dbchar");
  system("wp config set --quiet DB_COLLATE $dbcoll");

}


	/**
	 * rwp_check_file
	 *
	 * Returns true or false after pinging file on Rewind WP
	 *
	 * @since 1.0.0
	 * @param $file
	 */

public static function rwp_check_file($file) { 
	if(!empty($file)) { return self::rwp_api_call("check", $file); }
}

	/**
	 * rwp_check_db
	 *
	 * Returns true or false after pinging for a database file on Rewind WP
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_check_db() { 
	$zipdb = strtolower(DB_NAME).'.zip';
	return self::rwp_check_file($zipdb); 
}




/**
* rwp_currentip
*
* Current ip of website
*
* @since 1.0.0
* @param
*/
public static function rwp_currentip() {
 return $_SERVER['REMOTE_ADDR'];
}



/**
* rwp_currenturl
*
* Current url of page
*
* @since 1.0.0
* @param
*/
public static function rwp_currenturl() {
 return $_SERVER['REQUEST_URI'];
}

	/**
	 * rwp_api_call
	 *
	 * Make a call to the RWP API // upload, delete, etc.
	 *
	 * @since 1.0.0
	 * @param @action = check,clearcache,bunnyhost,RWP,upload,delete @input = filename
	 */

public static function rwp_api_call($action, $input) {
	//sanitize our action and input fields just in case
   $action = sanitize_text_field($action);
   $input = sanitize_text_field($input);

	//	depending on the action, send a remote request to Rewind WP to set or retrieve data
	//	these are a list of built-in commands that get sent to Rewind WP
   switch ($action) {
        case "apikey":
        case "disconnect":
        case "download":
        case "getbackup":
        case "getduration":
        case "getlastrewind":
        case "getlastsnap":
        case "getnextrewind":
        case "getrewind":
        case "status":
        case "subscription":
        case "wpconfig":
            $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action;
        break;
        case "formatseo":
        case "upload":
	      $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action.'&filename='.esc_url_raw($input);
	break;
        case "check":
        case "delete":
      	      $filterfile = rwp_remove_protocol(esc_url_raw($input));
	      $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action.'&filename='.$filterfile;
	break;
        case "connect":
            $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action.'&meta='.RWP_SITE_URL;
        break;
        case "setbackup":
	case "setrewind":
            $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action.'&active='.$input;
	break;
        case "setduration":
            $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action.'&delay='.$input;
        break;
        case "setlastrewind":
        case "setlastsnap":
        case "setnextrewind":
            $url = 'https://rewindwp.com/data?key='.self::rwp_api_key().'&action='.$action.'&dtstamp='.urlencode($input);
        break;
   }

   if(!empty($url)) { 
       //	sending api request to Rewind WP to send and receive data
       $getjson = wp_safe_remote_get(esc_url_raw($url));
	//	make sure we actually have an array that was returned
       if ( is_array( $getjson ) ) {
		//	use wordpress native feature to decode json
            $rjson = json_decode($getjson["body"], true);
		//	if the output is set then display the output message
            if(isset($rjson["output"])) { $message = $rjson["output"]; } else { $message = NULL; }
       }

    }

    //	make sure there is something to send back
   if(isset($message) && !empty($message)) { return $message; } 
 }


	/**
	 * rwp_sectohr
	 *
	 * Convert seconds to hours and minutes
	 *
	 * @since 1.0.0
	 * @param @seconds the number of seconds to convert
	 */

public static function rwp_sectohr($seconds) {
  $hours = floor(($seconds / 60) % 60);
  $minutes = $seconds % 60;
  if($hours == 1 && $minutes == 1) { 
  	return "$hours hour, $minutes minute";
  } else {
	return "$hours hour, $minutes minutes";
  }
}

	/**
	 * rwp_unzipFile
	 *
	 * Unzip files from folder
	 *
	 * @since 1.0.0
	 * @param $source
	 */


public static function rwp_unzipFile($source) {

    //	make sure zip extension is loaded
    if ( !extension_loaded('zip') ) {
        return false;
    } 

    //	determine if we should even be in here
    if(empty($source) || !file_exists($source)) { return false; }

    //	get the seo format
    $rwp_seo_file = self::rwp_seo_file($source);

    //	unzip the file
    system("unzip -ouq ".$source." -d /");

    //  return that the job was done
    return true;

}

	/**
	 * rwp_zipFile
	 *
	 * Makes zip from folder
	 *
	 * @since 1.0.0
	 * @param $source, $destination, 
	 */


public static function rwp_zipFile($source, $destination, $target)
{
    //	make sure zip extension is loaded
    if ( !extension_loaded('zip') ) {
        return false;
    } 

    //	determine if we should even be in here by checking:

    //	make sure there is a source and the file exists
    if(empty($source) || !file_exists($source)) { return false; }

    //	an existing zip file doesn't always overwrite, so delete it beforehand
    if(file_exists($destination)) { unlink($destination); }

    //	zip the file
    system("cd ".$target."; zip -roq ".$destination." ".$source);

    //  return that the job was done
    return true;
}


	/**
	 * rwp_saveDatabase
	 *
	 * save entire database with wp export
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_saveDatabase($file) {
   //	make sure passed in string is not empty
   if(empty($file)) { return false; }

    //	assign variables
    $file = strtolower($file);
    $sqlfile = strtolower($file.'.sql');
    $savefile = ABSPATH.$sqlfile;
    $zipfile = ABSPATH.$file.'.zip';
    $nopathzip = $file.'.zip';
    $zippedfile = $file.'.zip';
    $fullfile = RWP_SITE_URL.'/'.$zippedfile;
    
    //	export database
    system("wp db export --quiet ../".$sqlfile);

    self::rwp_zipFile($savefile, $zippedfile, ABSPATH); //	zip it

    //	if the file exists...
    if(file_exists($savefile)) { 
	self::rwp_delete_file($nopathzip); //	delete the file if it exists
     	self::rwp_upload_file($fullfile); //	upload it
	if(file_exists($savefile)) { unlink($savefile); }  //	delete the sql file
	return true;
    } else {
	return false;
    }

}

	/**
	 * rwp_clean_media_uploads
	 *
	 * compares the databse against the files in the uploads folder and deletes anything that is irrelevant
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_clean_media_uploads() {
	//	get all images from current database
	$imgids = get_posts(
   	 array(
        	'post_type'      => 'attachment',
        	'post_mime_type' => 'image',
        	'post_status'    => 'inherit',
        	'posts_per_page' => - 1,
       	 	'fields'         => 'ids',
    	) );

	//	array map
	$mediaarray = array_map( "wp_get_attachment_url", $imgids );

	//	grab uploads folder and put it all into an array
	$path = RWP_UPLOAD_DIR.'/';
	$filesarray = array();
	//	so we can use list_files function
	if (!class_exists('WP_Filesystem')) { include_once( ABSPATH . ADMIN_COOKIE_PATH . '/includes/file.php' ); }
        $files = list_files($path);
        foreach ($files as $i => $file) {
		if(is_dir($file)) { continue; }
		$filesarray[] = $file;
        }

	//	comparing the media array (WordPress database) to the File array (Media folder)
	$checkarray = array_diff($filesarray, $mediaarray);

	foreach($checkarray as $delete_file) { 
		//	if the file exists in the folder but not the database...
		if(file_exists($delete_file)) { unlink($delete_file); }
	}
	
}

	/**
	 * rwp_wpcontent_clean
	 *
	 * cleans up inactive themes & plugins 
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_wpcontent_clean() {
    system("wp plugin delete --quiet $(wp plugin list --status=inactive --field=name)");
    system("wp theme delete --quiet $(wp theme list --status=inactive --field=name)");
}



	/**
	 * rwp_rewind_now
	 *
	 * Force Rewind WP to restore snapshot
	 *
	 * @since 1.0.0
	 * @param 
	 */

public static function rwp_rewind_now() {

		//check for database
	$dbcheck = self::rwp_check_db();

		//if none... get out of here
	if(empty($dbcheck)) { return false; }

	//	assign seo file paths to variables
	$rwp_wpc = self::rwp_seo_file(strtolower(RWP_DATA_URL.RWP_CONTENT_DIR.'.zip'));
	$abs_wpc = self::rwp_seo_file(strtolower(ABSPATH.RWP_CONTENT_DIR.'.zip'));
	$abs_wpc_path = self::rwp_seo_file(strtolower(ABSPATH.RWP_CONTENT_DIR));
	$rwp_db = self::rwp_seo_file(strtolower(RWP_DATA_URL.DB_NAME.'.zip'));
	$abs_db = self::rwp_seo_file(strtolower(ABSPATH.DB_NAME.'.zip'));
	$raw_abs_db = strtolower(ABSPATH.DB_NAME.'.sql');

	//	any existing files will be deleted so we can download the new one and import it
	if(file_exists($raw_abs_db)) { unlink($raw_abs_db); }
	if(file_exists($abs_db)) { unlink($abs_db); }

	//	download the database
	$download_db = self::rwp_download_file($rwp_db);

	//	unzip and insert database then delete file
	if(file_exists($abs_db)) { 
		self::rwp_unzipFile($abs_db);
		system("wp db import --quiet --field=force $raw_abs_db");
		//clean up our mess
		if(file_exists($raw_abs_db)) { unlink($raw_abs_db); }
		if(file_exists($abs_db)) { unlink($abs_db); }
	}

	//	send data about wordpress timestamp for last rewind to rewindwp
	self::rwp_last_rewind();

	//	update data on the rewinds count
	self::rwp_rewinds_count();

	//	if not pro then get out
        if(!self::rwp_sub_pro(self::rwp_api_key())) { return false; }

	//	wp-content
	if(file_exists($abs_wpc)) { unlink($abs_wpc); }
	//	download the wp-content zip file
	$download_wpcontent = self::rwp_download_file($rwp_wpc);
	if(file_exists($abs_wpc)) {
		//	wipe out wp-content directory
		self::rwp_wpcontent_clean();
		//	clear out media
		self::rwp_clean_media_uploads();
		//	replace it with the zipped file
		self::rwp_unzipFile($abs_wpc);
		//	clean up our mess 
		if(file_exists($abs_wpc)) { unlink($abs_wpc); }
	}

	self::rwp_wpconfig_restore();

}

}
?>
