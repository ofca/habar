<?php

namespace Kohana\IO;

class Directory
{
	public static function create($dir, $chmod = 0777, $recursive = TRUE)
	{
		// If directory already exists, return TRUE
		if (is_dir($dir))
		{
			return TRUE;
		}

		// Create directory
		mkdir($dir, $chmod, TRUE);

		// Set permissions (must be manually set to fix umask issues)
		chmod($dir, $chmod);		
	}
}