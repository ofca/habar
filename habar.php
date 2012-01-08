<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

// Define path to Habar
define('HABAR_DIR', __DIR__.'/modules/habar/vendor/Habar'.DIRECTORY_SEPARATOR);

// Include loader
include HABAR_DIR.'loader.php';

use Habar\Habar;

/**
 * Configuration
 */ 
$config = 
	array(
		'definitions' => array(
			'example'	=> array(
				'action'	=> 'resize',
				'width'		=> 300,
				'height'	=> 200
			),
			'example2'	=> array(
				'action'	=> 'smartResize',
				'width'		=> 292,
				'height'	=> 200
			),
			'example3'	=> array(
				'action'	=> 'smartResize',
				'width'		=> 292,
				'height'	=> 200,
				'cropMiddle'=> TRUE
			),
		),
		/**
		 * List of allowed resources to serve. 
		 */
		'allowed' => array(
			'jpg'	=> 'image',
			'jpeg'	=> 'image',
			'gif'	=> 'image',
			'png'	=> 'image',
			'css'	=> 'css',
			'js'	=> 'js'
		),
		'base_url'	=> '/habar/',
		/**
		 * Application root directory. Must ends with '/'.
		 * Eg. /var/www/my-application/ 
		 */
		'root_dir'	=> __DIR__.DIRECTORY_SEPARATOR,
		'media_dir'	=> array('media/')
	);

echo Habar::factory($_SERVER['REQUEST_URI'], $config)->serve();