<?php
class RWP 
{

	/**
	 * getOptions
	 *
	 * Returns the array of all the options with their default values in case they are not set
	 *
	 * @since 1.0.0
	 * @param 
	 */

	public static function getOptions() {
        return wp_parse_args(
			get_option('RWP'),
			array(
				"api_key" => "",
                                "status" => "",
                                "duration" => "",
                                "wpconfig" => "",
                                "wpcontent" => ""
			)
		);
    }	

	/**
	 * getOption
	 *
	 * Returns the option value for the given option name. If the value is not set, the default is returned
	 *
	 * @since 1.0.0
	 * @param 
	 */

	public static function getOption($option)
	{
		$options = RWP::getOptions();
		return $options[$option];
	}

	/**
	 * validateSettings
	 *
	 * Returns the option value for the given option name. If the value is not set, the default is returned
	 *
	 * @since 1.0.0
	 * @param 
	 */
	
	public static function validateSettings($data)
	{ 


             //	setting the hostname if there is data
             //	clean, validate, escape the input from user

             //	set the API key and all data
 if(isset($data['api_key'])) { $apikey = $data['api_key']; } else { $apikey = NULL; }

	//	set status
 if(isset($data['status'])) { $status = $data['status']; } else { $status = NULL; }

	//	if disconnected, clear out the cron and officially report to Rewind WP that the status is disconnected
 if(empty($status)) { 
	rwp_Functions::rwp_clear_cron();
        rwp_Functions::rwp_disconnect();
  } else { 
	//	report to Rewind WP that the status is connected
        rwp_Functions::rwp_connect();
 }

	//	check if duration is set and send data to Rewind WP
 if(isset($data['duration'])) { 
	$duration = $data['duration']; 
	rwp_Functions::rwp_set_duration($duration);
  } else {
	//	clear out everything
	$duration = 0; 
 	rwp_Functions::rwp_set_duration(0);
  }

	//	backup the wpconfig file, data sent to Rewind WP
 if(isset($data['wpconfig'])) { 
	$wpconfig = $data['wpconfig']; 
	rwp_Functions::rwp_set_backup(1);
} else { 
	//	clear backup
	$wpconfig = NULL; 
	rwp_Functions::rwp_set_backup(0);
}
 
	//	backup the wp-content folder, data sent to Rewind WP
 if(isset($data['wpcontent'])) { 
	$wpcontent = $data['wpcontent']; 
		rwp_Functions::rwp_set_rewind(1);
 } else { 
	//	clear the rewind
		$wpcontent = NULL; 
 }

          //	returning an array with the status, duration, API key, and wpconfig,wpcontent option
		return array(
				"api_key" => sanitize_text_field($apikey),
                                "status" => $status,
                                "duration" => $duration,
                                "wpconfig" => $wpconfig,
                                "wpcontent" => $wpcontent
			);
	}

}


class RWPSettings
{
	/**
	 * initialize
	 *
	 * Initialize the settings page
	 *
	 * @since 1.0.0
	 * @param 
	 */
	

	public static function initialize()
	{
  

        add_submenu_page( 'tools.php', 'Rewind WP', 'Rewind WP', 'manage_options', 'rewindwp', 	array(						
				'RWPSettings',
				'rwp_options_page'
			)  );

		//register RWP into WordPress
		register_setting('RWP', 'RWP', array("RWP", "validateSettings"));

	}


	/**
	 * rwp_options_page
	 *
	 * Display the options page
	 *
	 * @since 1.0.0
	 * @param 
	 */
	
	public static function rwp_options_page()
	{ 
		$options = RWP::getOptions();
                //load up our logo and UI:: where the magic is set!
		?> 
		<div style="width: 550px; padding-top: 20px; margin-left: auto; margin-right: auto; margin: auto; text-align: center; position: relative;">
			<a href="https://rewindwp.com" target="_blank"><img border="0" style="width:20%;" src="<?php echo plugins_url('assets/rwp-logo.png', dirname(__FILE__) ); ?>"></a>
			<?php
                              // validation of RWP key
				if(empty(rwp_Functions::rwp_full_validation_apikey()))
				{
					echo '<h2>Enable Rewind WP</h2>';
				}
				else 
				{
					echo '<h2>Configure Rewind WP</h2>';
				}
			?>
			
			<form id="rwp_options_form" method="post" action="options.php">
				<?php settings_fields('RWP'); ?>
			
				<!-- Simple settings -->
				<div id="rwp-simple-settings">
<?php

   //	grab status
   $status = rwp_Functions::rwp_get_status();

   //	grab duration
   $duration = rwp_Functions::rwp_get_duration(); 

   //	wp config settings
   $wpconfig = rwp_Functions::rwp_backup_status();

   //	wp content settings
   $wpcontent = rwp_Functions::rwp_rewind_status();

   //	next rewind timestamp
   $nextrewind = rwp_Functions::rwp_get_nextrewind();
   if($duration == 0) { $nextrewind = NULL; }

   //	last snapshot timestamp
   $lastsnap = rwp_Functions::rwp_get_lastsnap();

   //	check to see if database exists
   $checkdb = rwp_Functions::rwp_check_db();

   //	clear initial message
   $initmsg = NULL;

   //	if the url contains init command, display that command is running
   if(preg_match('/rewind=init/i', rwp_Functions::rwp_currenturl())) { 
	if($duration < 1) { 
	      $initmsg = sprintf(__('<p><span style="color:green;">Cronjob is running.</span></p>', 'Rewind WP')); 
       		 echo __( $initmsg, 'Rewind WP' );
	}
   }

   //	if snapshot display a message
   if(preg_match('/snapshot/i', rwp_Functions::rwp_currenturl())) {
     $initmsg = sprintf(__('<p><span style="color:green;">A snapshot has been created.</span></p>', 'Rewind WP')); 
       		 echo __( $initmsg, 'Rewind WP' );
   }


   //	if the url contains forcerewind command, display that command run to download and restore
   if(preg_match('/forcerewind/i', rwp_Functions::rwp_currenturl())) { 
      $initmsg = sprintf(__('<p><span style="color:green;">Force Rewind successfully executed.</span></p>', 'Rewind WP')); 
        echo __( $initmsg, 'Rewind WP' );
   }
 
   //	always checking to ensure the API key is not empty and being validated
  if(!empty(rwp_Functions::rwp_full_validation_apikey())) {
        $initmsg = '<p>View your account at <a href="https://rewindwp.com" target="_blank">Rewind WP</a>.</p>';
        echo __( $initmsg, 'Rewind WP' );
   } else {
        $initmsg = '<p><a href="https://rewindwp.com" target="_blank">Sign up for an account at Rewind WP</a> and get your API key.</p>';
        echo __( $initmsg, 'Rewind WP' );
   }

  //	if api key valid
  if(!empty(rwp_Functions::rwp_full_validation_apikey())) {
	if(!empty($nextrewind) && !empty($status)) { 
		//	current wordpress time
		$dtinit = strtotime(current_time( 'mysql' ));
		//	current rewind time
		$dtend = strtotime($nextrewind);
		//	get difference
		$interval  = abs($dtend - $dtinit);
		//	round some minutes
		$minutes   = round($interval / 60);
		//	get timestamp of last rewind
		$getlastrewind = rwp_Functions::rwp_get_lastrewind();
		//	officially set the label of Rewind WP
		$lastrewind = (!empty($getlastrewind) ? rwp_Functions::rwp_get_lastrewind() : 'N/A');
		//	aesthetics
		if($minutes == 1) { $mins = ' minute'; } else { $mins = ' minutes'; }
		//	current timestamp vs. running now
		if($minutes > 0) {
		        $initmsg = '<p><strong>Current Timestamp: '.current_time( 'mysql' ).'<br>Last Rewind: '.$lastrewind.'<br>Next Rewind: '.$nextrewind.'<br>Running in: '.$minutes.' '.$mins.'</strong></p>';
		} else {
		        $initmsg = '<p><strong>Current Timestamp: '.current_time( 'mysql' ).'<br>Last Rewind: '.$lastrewind.'<br>Next Rewind: '.$nextrewind.'<br>Running Now</strong></p>';
		}
		//	display the init msg
    	   	echo __( $initmsg, 'Rewind WP' );
	} else { 
		//	otherwise if nothing is running, just display a timestamp
	        $initmsg = '<p><strong>Current Timestamp: '.current_time( 'mysql' ).'</strong></p>';
		//	display the init msg
    	   	echo __( $initmsg, 'Rewind WP' );

	}
  }


?>
					<table class="form-table">
						<tr valign="top">
						<th scope="row">
							API Key
						</th>
						<td>
							<input type="text" name="RWP[api_key]" id="rwp_api_key" value="<?php echo RWP::getOption('api_key'); ?>" size="64" class="regular-text code" maxlength="12" style="width:125px;" />
							<p class="description">The API Key is used to activate this plugin.</p>
						</td>
					</tr>



					</table>
				</div>

<?php
//the fields only become visible with a valid RWP key
if(!empty(rwp_Functions::rwp_full_validation_apikey())) { ?>

				<table id="rwp-settings" class="form-table">


					<tr valign="top">
						<th scope="row">
							Status
						</th>
						<td>
					 <select name="RWP[status]"> 

<?php
if(empty($status)) {
	//disable everything if set to inactive
    rwp_Functions::rwp_clear_cron();
    rwp_Functions::rwp_disconnect();
    echo '<option value="0" selected>Inactive</option><option value="1">Active</option>';
} else {
	//enable everything if set to active
    rwp_Functions::rwp_connect();
    echo '<option value="1" selected>Active</option><option value="0">Inactive</option>';
} 
//if active
if(!empty($status)) { 
?>
                                         </select>
<p class="description">Set the status of Rewind WP.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							Duration
						</th>
						<td>
		<?php
echo '<select name="RWP[duration]">';
if(rwp_Functions::rwp_sub_pro(rwp_Functions::rwp_api_key())) {
	//loop thru the minutes in increments of 5
	for($rwpcount=0; $rwpcount<1440; $rwpcount+=4) {
	$rwpsectomin = ($rwpcount * 60);  
	   	if(strcmp($rwpsectomin, $duration) === 0) { 
   			echo '<option value="'.$rwpsectomin.'" selected>'.rwp_Functions::rwp_sectohr($rwpcount).'</option>';
   		} else {
   			echo '<option value="'.$rwpsectomin.'">'.rwp_Functions::rwp_sectohr($rwpcount).'</option>';
   		}
   		$rwpcount++;
	} 
} else { 
	//loop thru the minutes in increments of 30
	for($rwpcount=0; $rwpcount<65; $rwpcount+=29) {
	$rwpsectomin = ($rwpcount * 60);  
	   	if(strcmp($rwpsectomin, $duration) === 0) { 
     			echo '<option value="'.$rwpsectomin.'" selected>'.rwp_Functions::rwp_sectohr($rwpcount).'</option>';
   		} else { 
     			echo '<option value="'.$rwpsectomin.'">'.rwp_Functions::rwp_sectohr($rwpcount).'</option>';
   		}
  		$rwpcount++;
 	}
}
echo '</select>';
?>

<p class="description">Set the time that will pass before each rewind.</p>


						</td>
					</tr>




					<tr valign="top">
						<th scope="row">
							File Protection
						</th>
						<td>
		<?php

//	validate
if(rwp_Functions::rwp_sub_pro(rwp_Functions::rwp_api_key())) { 
	//	wp content config protection
	if($wpcontent == 0) { 
    		echo '<select name="RWP[wpcontent]">';
    		echo '<option value="0" selected>No</option>';
    		echo '<option value="1">Yes</option>';
    		echo '</select>';
	} else {
    		echo '<select name="RWP[wpcontent]">';
    		echo '<option value="1" selected>Yes</option>';
    		echo '<option value="0">No</option>';
    		echo '</select>';
	}
} else { // pro
    echo '<select disabled>';
    echo '<option selected>Pro Only</option>';
    echo '</select>'; 
}
?>
<p class="description">All files within wp-content are protected.</p>

						</td>
					</tr>



					<tr valign="top">
						<th scope="row">
							WP-Config Protection
						</th>
						<td>
		<?php

	//	validate
if(rwp_Functions::rwp_sub_pro(rwp_Functions::rwp_api_key())) { 
if($wpconfig == 0) {
	//	wp config settings protection 
    echo '<select name="RWP[wpconfig]">';
    echo '<option value="0" selected>No</option>';
    echo '<option value="1">Yes</option>';
    echo '</select>';
} else {
    echo '<select name="RWP[wpconfig]">';
    echo '<option value="1" selected>Yes</option>';
    echo '<option value="0">No</option>';
    echo '</select>';
}
} else {
    echo '<select disabled>';
    echo '<option selected>Pro Only</option>';
    echo '</select>'; 
}
?>

<p class="description">Backup your WP-Config file</p>
						</td>
					</tr>






		<tr valign="top">
						<th scope="row">
							Force Rewind
						</th>
						<td>
				<?php
				//	force a rewind
		                 echo (!empty($checkdb) ? '<a href="?page=rewindwp&forcerewind" class="button">Rewind Now</a>' : '<button class="button" disabled>Rewind Now</button>');
				?>
				<p class="description">Rewind your website or <a href="?page=rewindwp&rewind=snapshot">Create Snapshot</a>.
				<?php
				//	display last snapshot timestamp
					if(!empty($lastsnap) && !empty($checkdb)) { echo '<br><small>Last snapshot: '.$lastsnap.'</small>'; }
				?>
</p>
						</td>
					</tr>

		<tr valign="top">
						<th scope="row">
							Rewind
						</th>
						<td>
			<?php
		//	if there is no database, give user option to create one!
		if(empty($checkdb)) { 
			echo '<a href="?page=rewindwp&rewind=snapshot" class="button">Create Snapshot</a>'; 
		} else { 
		//	if a database already exists, then we want to know if we are in progress or not
			if(empty($nextrewind)) { 
       	        		echo '<a href="?page=rewindwp&rewind=init" class="button">Initialize</a>';
			} else {
       	        		echo '<a href="?page=rewindwp&rewind=stop" class="button">Stop Rewind</a>';
			}
		}
			?>

<p class="description">Start or stop the automatic rewind.</p>

						</td>
					</tr>


		<?php

}

		  //	checking to see if server has zip capabilities and wp-cli
		  //	if user does not, this plugin won't work
		  //	display warning only, does not prevent functionality as it could just be a false positive
	          $checkzip = rwp_Functions::rwp_check_zip();
	          $checkcli = rwp_Functions::rwp_check_wpcli();
		  if(!$checkzip || !$checkcli) { 
		?>
		<tr valign="top">
						<th scope="row">
							Warnings
						</th>
						<td>
		<?php
		  if(!$checkzip) { echo '<p><span style="color:red;font-weight:bold;">Zip extension not detected.</span>'; }
		  if(!$checkcli) { echo '<p><span style="color:red;font-weight:bold;">WP-CLI extension not detected.</span>'; }
		?>

	<p class="description">This plugin may not work due to these warnings.</p>

						</td>
			</tr>
		<?php
			}
		?>


	<tr valign="top">
						<th scope="row">
							License
						</th>
						<td>
		<?php
		//	checking to see if free or pro version
		if(rwp_Functions::rwp_sub_pro(rwp_Functions::rwp_api_key())) { 
			echo 'Pro';
		} else { 
			echo 'Free (<a target="_blank" href="https://rewindwp.com/subscription?rwpid='.rwp_Functions::rwp_api_key().'&nonce='.wp_generate_uuid4().'">Upgrade</a>)';
		}
		?>

<p class="description">Your version of Rewind WP.</p>


						</td>
					</tr>

			</table>
<?php } ?>
				<div>
					<p class="submit">
						<input type="submit" name="rwp-save-button" id="rwp-save-button" class="button submit" style="color:#000;font-weight:bold;" value="<?php echo (empty(RWP_Functions::rwp_full_validation_apikey()) ? 'Enable Rewind WP' : 'Update Settings'); ?>"> 
					</p>						 


<p><small>Check out the <a href="<?php echo RWP_PLUGIN_URLDIR.RWP_PLUGIN_NAME; ?>/readme.txt" target="_blank">Readme file</a> for additional documentation.</small></p>
				</div>

			</form>
		</div><?php
	}
}

//	always checking on the wpconfig backup settings
rwp_Functions::rwp_wpconfig_backup(); 

$duration = rwp_Functions::rwp_get_duration();

if($duration < 1) { 
//	set next rewind to 0
    rwp_Functions::rwp_set_nextrewind(0);
}

//	rewind has been initiated
if(preg_match('/rewind=init/i', rwp_Functions::rwp_currenturl())) {
	$duropt = RWP::getOption('duration'); 
	if($duration > 0 || $duropt > 0) { 
		rwp_Functions::rwp_set_nextrewind(1);
	} else {
		rwp_Functions::rwp_set_nextrewind(0);
	}
}

//	unschedule the cron job from Rewind WP
if(preg_match('/rewind=stop/i', rwp_Functions::rwp_currenturl())) { 
	rwp_Functions::rwp_clear_cron();
	rwp_Functions::rwp_set_nextrewind(0);
}

//	force rewind has been initiated
if(preg_match('/forcerewind/i', rwp_Functions::rwp_currenturl())) { 
   	rwp_Functions::rwp_rewind_now();
}


//	create a snapshot only, used for the rewind now button if no snapshot exists
if(preg_match('/rewind=snapshot/i', rwp_Functions::rwp_currenturl())) { 
   	rwp_Functions::rwp_create_snapshot();
}

?>
