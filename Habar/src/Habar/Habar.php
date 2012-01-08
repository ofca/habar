<?php

namespace Habar;

use \Kohana\Arr;
use \Kohana\Image;

/**
 *  mediacache/media/uploads/1.article-thumb.jpg
 */ 

class Habar
{
	protected $_definitions = array();
	
	protected $_root_dir;
	protected $_cache_dir = 'mediacache/';
	protected $_media_dir = array();

	protected $_allowed = array();
	/**
	 * Path to requested resource
	 * @var	string
	 */ 
	protected $_path;
	protected $_file;
	protected $_base_url = '/';
	protected $_definition;

	protected $_directory_pattern = '-_a-z0-9';
	protected $_filename_pattern = '-_a-z0-9';

	public static $mimes = array(
		'bmp'   => array('image/bmp'),
		'gif'   => array('image/gif'),
		'ico'   => array('image/x-icon'),
		'jpe'   => array('image/jpeg', 'image/pjpeg'),
		'jpeg'  => array('image/jpeg', 'image/pjpeg'),
		'jpg'   => array('image/jpeg', 'image/pjpeg'),
		'png'   => array('image/png', 'image/x-png'),
		'js'    => array('application/x-javascript'),
		'json'  => array('application/json'),
		'css'   => array('text/css')
	);

	/**
	 * Output sended to browser.
	 */ 
	protected $_response = array(
		'headers'	=> array(),
		'body'		=> NULL
	);

	public static function factory($path, array $config)
	{
		return new self($path, $config);
	}

	public function __construct($path, array $config)
	{
		// Append configs
		$allowed_config = 
			array('definitions', 'allowed', 'root_dir', 'cache_dir', 
					'media_dir', 'directory_pattern', 'filename_pattern', 'base_url');

		foreach ($allowed_config as $key)
		{
			if (array_key_exists($key, $config))
			{
				$this->{'_'.$key} = $config[$key];
			}
		}

		$path = substr($path, strlen($this->_base_url), strlen($path));


		// Validate
		if ( ! ($this->_file = $this->valid($path)) OR ! array_key_exists($this->_file['extension'], $this->_allowed))
		{
			return $this->file_not_found();
		}

		// Remove from path name of the cache directory
		$this->_path = substr($path, strlen($this->_base_url.$this->_cache_dir)+1, strlen($path));

		// Get method by resource type
		$method = $this->_allowed[$this->_file['extension']];

		// Start processing
		$this->$method();
	}

	public function file_not_found() { echo 'file not found'; }

	public function valid($path)
	{
		// eg.
		//
		// mediacache/media/path/to/file/image.article-thumb.jpg
		// 
		// where:
		//
		// "mediacache" is Habar::$cache_dir
		// "media" is Habar::$media_dir
		// "path/to/file" is <dirname>
		// "image" is <filename>
		// "article-thumb" is <definition>
		// "jpg" is <extension>
		$regex = '#^
				(?<path>'.str_replace('/', '\/', $this->_cache_dir).'
					(?<dirname>	
						(?:'.implode('|', str_replace('/', '\/', $this->_media_dir)).')
						(?:['.$this->_directory_pattern.']+\/)*
					)
					(?<filename>['.$this->_filename_pattern.']+)\.
					(?<definition>'.implode('|', array_keys($this->_definitions)).')\.
					(?<extension>'.implode('|', array_keys($this->_allowed)).')
				)
		$#ix';
		
		if ( ! preg_match($regex, $path, $file))
		{
			// Return empty file
			return FALSE;
		}

		// Find the file parts
		$file = 
			array_intersect_key(
				$file, 
				array_flip(
					array(
						'path', 'dirname', 'filename', 'definition', 'extension'
					)
				)
			);
		
		$this->_definition = $this->_definitions[$file['definition']];

		$file['origin_fullpath'] = 
			$file['dirname'].$file['filename'].'.'.$file['extension'];

		return $file;
	} // eo valid

	public function image()
	{
		// Import locally		
		$def = $this->_definition;
		$file = $this->_file;

		// Check if origin file exists
		if ( ! is_file($file['origin_fullpath']))
		{
			return FALSE;
		}

		$image_actions = array('resize', 'smartResize');

		if ( ! array_key_exists('action', $def) 
				OR ! in_array($def['action'], $image_actions))
		{
			return FALSE;
		}

		$method = 'image_'.$def['action'];

		if ( ! $this->$method())
		{
			$this->image_empty();
		}

		$this->image_response();
	}

	public function image_resize()
	{
		// Import locally
		$file = $this->_file;
		$def = $this->_definition;

		$width = (int) Arr::get($def, 'width');
		$height = (int) Arr::get($def, 'height');
		$master = Arr::get($def, 'master', 'auto');
		
		$resize_modes = array(
			'none'		=> Image::NONE,
			'width'		=> Image::WIDTH,
			'height'	=> Image::HEIGHT,
			'auto'		=> Image::AUTO,
			'inverse'	=> Image::INVERSE
		);

		if ( ! array_key_exists($master, $resize_modes))
		{
			$master = NULL;
		}

		try
		{
			// Create cache directory
			\Kohana\IO\Directory::create($this->_root_dir.$file['dirname']);

			$image = Image::factory($file['origin_fullpath']);

			$image
				->resize($width, $height, $master)
				->save($this->_root_dir.$file['path']);
		}
		catch (Exception $e)
		{
			return FALSE;
		}

		return TRUE;
	}

	public function image_smartResize()
	{
		// Import locally
		$file = $this->_file;
		$def = $this->_definition;

		$width = (int) Arr::get($def, 'width');
		$height = (int) Arr::get($def, 'height');

		try
		{
			// Create cache directory
			\Kohana\IO\Directory::create($this->_root_dir.$file['dirname']);

			$image = Image::factory($file['origin_fullpath']);

			$image->resize($width, $height, Image::INVERSE);

			if ($image->width > $width OR $image->height > $height)
			{
				$x = $y = 0;

				if (Arr::get($def, 'cropMiddle') === TRUE)
				{
					$x = ($image->width > $width) ? (int) ($image->width - $width) / 2 : 0;
					$y = ($image->height > $height) ? (int) ($image->height - $height) / 2 : 0;
				}

				$image->crop($width, $height, $x, $y);
			}

			$image->save($this->_root_dir.$file['path']);
		}
		catch (Exception $e)
		{
			return FALSE;
		}

		return TRUE;
	}


	public function image_response()
	{
		// Import locally
		$file = $this->_file;

		$headers = array(
			'Content-Type'		=> self::$mimes[$file['extension']],
			'Content-Length'	=> filesize($file['path']),
			'Last-Modified'		=> gmdate('D, d M Y H:i:s', filemtime($file['path'])),
			'Cache-Control'		=> 'max-age=29030400',	// one year
			'Pragma'			=> 'public',
			'Expires'			=> gmdate('D, d M Y H:i:s', time() + 29030400) // one year
		);

		$this->_response['headers'] += $headers;
		$this->_response['body'] = file_get_contents($file['path']);
	}

	public function serve()
	{
		if ( ! headers_sent())
		{
			foreach ($this->_response['headers'] as $header => $value)
			{
				header($header.': '.$value);
			}
		}

		return $this->_response['body'];
	}
}