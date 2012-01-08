<?php

class FileSystem
{
	/**
	 * Removes directory.
	 *
	 * @param	string	Path to directory
	 * @param	bool	Delete all children?
	 * @return	bool
	 */
	public static function rmdir($dir, $recursive = TRUE)
	{
		if (is_dir($dir))
		{ 
			$objects = scandir($dir); 
			
			foreach ($objects as $object)
			{ 
				if ($object != "." && $object != "..")
				{ 
					if (filetype($dir."/".$object) == "dir")
					{
						if (self::rmdir($dir."/".$object) === FALSE)
						{
							return FALSE;
						}
					}
					else
					{
						if (self::rmfile($dir."/".$object))
						{
							return FALSE;
						}
					}
				} 
			}
			
			reset($objects); 
			return rmdir($dir); 
		}

		return NULL;
	} // eo rmdir

	public static function cleardir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);

			foreach ($objects as $object)
			{
				if ($object == '.' OR $object == '..')
				{
					continue;
				}

				if (filetype($dir.'/'.$object) == 'dir')
				{
					self::rmdir($dir.'/'.$object);
				}
				else
				{
					self::rmfile($dir.'/'.$object);
				}
			}
		}
	} // eo cleardir

	/**
	 * Creates directory.
	 *
	 * @param	string		Path to new directory
	 * @param	integer		chmod
	 * @param	bool		Allow the create of nested directories specified in the path?
	 * @return	bool
	 */
	public static function mkdir($dir, $chmod = 0777, $recursive = TRUE)
	{
		// If directory already exists, return TRUE
		if (is_dir($dir))
		{
			return TRUE;
		}

		$result = mkdir($dir, 0777, TRUE);

		if ($result)
		{
			chmod($dir, 0777);
		}

		return $result;
	} // eo mkdir

	/**
	 * Deletes file.
	 *
	 * @param	string	Path to file
	 * @return	bool
	 */
	public static function rmfile($path)
	{
		if (file_exists($path))
		{
			return unlink($path);
		}

		return NULL;
	} // eo rmfile

	/**
	 * Creates file.
	 *
	 * @param	string	Path to file
	 * @param	string	File contents
	 * @param	int		flag taken by file_put_contents function
	 * @return	bool
	 */
	public static function mkfile($path, $content, $flag = LOCK_EX)
	{
		$parts = pathinfo($path);
		// Create directories if not exists
		FileSystem::mkdir($parts['dirname']);
		// Create file
		return (bool) file_put_contents($path, $content, $flag);
	} // mkfile

	/**
	 * Includes php file with array inside. If file
	 * does not exists, it's created.
	 *
	 * @param	string	Path to file
	 * @return  bool
	 */
	public static function include_php_array($path)
	{
		if ( ! file_exists($path))
		{
			FileSystem::mkfile($path, PHP::array_file(array()));
		}

		return include $path;
	} // eo include_php_array
}