<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

// Define path to Habar
define('HABAR_DIR', realpath(dirname(__DIR__.'/modules/habar/vendor/Habar')).DIRECTORY_SEPARATOR);

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
				'width'		=> 200,
				'height'	=> 300
			),
			'example2'	=> array(
				'action'	=> 'smartcrop',
				'width'		=> 200,
				'height'	=> 200
			)
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
		/**
		 * Application root directory. Must ends with '/'.
		 * Eg. /var/www/my-application/ 
		 */
		'root_dir'	=> __DIR__,
		/**
		 * Path to directory with cache of media files.
		 */ 
		'cache_dir'	=> __DIR__.'mediacache/',
		'media_dir'	=> array('media/')
	);

echo Habar::factory($_SERVER['REQUEST_URI'], $config)->serve();