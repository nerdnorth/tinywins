<?php

if (!defined('ABSPATH')) die;

/**
 * Geolocation from IP Address.
 *
 * Provides geolocation.
 *
 * @since      1.1.0
 * @package    LogicHop
 * @subpackage LogicHop/includes/services
 */
	
class LogicHop_Geo_IP {
	
	/**
	 * Core functionality & logic class
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * GEO IP API URL
	 *
	 * https://github.com/fiorix/freegeoip
	 * http://freegeoip.net
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $api_url    API URL
	 */
	private $api_url;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.1.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic 		= $logic;
		$this->api_url 		= 'http://freegeoip.net/json/';
	}
	
	/**
	 * Retrieve Geolocation Data
	 *
	 * @since    	1.1.0
	 * @param      	string     	$ip       IP Address
	 * @return      object    	Geolocation object
	 */
	public function geolocate ($ip = '0.0.0.0') {
		
		$geo = $this->geo_object();
		
		if ($ip != '0.0.0.0') {
			$url = sprintf('%s%s', 
							$this->api_url, 
							$ip
						);
			$response = wp_remote_get($url);
		
			if (!is_wp_error($response)) {
				$data = (isset($response['body'])) ? json_decode($response['body'], false) : false;
				if (isset($data)) {
					$geo->Active = true;
					if (isset($data->ip)) 			$geo->IP 			= $data->ip;
					if (isset($data->country_code)) $geo->CountryCode 	= $data->country_code;
					if (isset($data->country_name)) $geo->CountryName 	= $data->country_name;
					if (isset($data->region_code)) 	$geo->RegionCode 	= $data->region_code;
					if (isset($data->region_name)) 	$geo->RegionName 	= $data->region_name;
					if (isset($data->city)) 		$geo->City 			= $data->city;
					if (isset($data->zip_code)) 	$geo->ZIPCode 		= $data->zip_code;
					if (isset($data->time_zone)) 	$geo->TimeZone 		= $data->time_zone;
					if (isset($data->latitude)) 	$geo->Latitude 		= $data->latitude;
					if (isset($data->longitude)) 	$geo->Longitude 	= $data->longitude;
					if (isset($data->metro_code)) 	$geo->MetroCode 	= $data->metro_code;
				}
			}
		}
		return $geo;
	}
	
	/**
	 * Generate Geolocation Object
	 *
	 * @since    	1.1.0
	 * @return      object    	Geolocation object skeleton
	 */
	public function geo_object () {
		$geo = new stdclass;
		$geo->Active		= false;
		$geo->IP 			= '0.0.0.0';
		$geo->CountryCode 	= '';
		$geo->CountryName 	= '';
		$geo->RegionCode 	= '';
		$geo->RegionName 	= '';
		$geo->City 			= '';
		$geo->ZIPCode 		= '';
		$geo->TimeZone 		= '';
		$geo->Latitude 		= 0;
		$geo->Longitude 	= 0;
		$geo->MetroCode 	= 0;
		return $geo;
	}
}















