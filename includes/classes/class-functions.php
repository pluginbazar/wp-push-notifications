<?php
/*
* @Author 		Jaed Mosharraf
* Copyright: 	2015 Jaed Mosharraf
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

class WPPN_Functions  {
	
	public function wppn_schedules(){
		
		$wppn_schedules = array(
			'daily' => array(
				'label' 	=> __( 'Daily', WPPN_TEXTDOMAIN ),
				'details' 	=> __( 'Once in a Day', WPPN_TEXTDOMAIN ),
				'interval'	=> 86400,
			),
			'weekly' => array(
				'label'		=> __( 'Weekly', WPPN_TEXTDOMAIN ),
				'details'	=> __( 'Once in a Week', WPPN_TEXTDOMAIN ),
				'interval'	=> 86400 * 7,
			),
			'biweekly' => array(
				'label'		=> __( 'Bi Weekly', WPPN_TEXTDOMAIN ),
				'details'	=> __( 'Once in every Two Weeks', WPPN_TEXTDOMAIN ),
				'interval'	=> 86400 * 7 * 2,
			),
			'monthly' => array( 
				'label'		=> __( 'Monthly', WPPN_TEXTDOMAIN ),
				'details'	=> __( 'Once in a Month', WPPN_TEXTDOMAIN ),
				'interval'	=> 86400 * 30,
			),
		);
			
		return apply_filters( 'wppn_filter_wppn_schedules', $wppn_schedules );
	}
	
} new WPPN_Functions();
