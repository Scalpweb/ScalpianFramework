<?php

class FileSystem
{

	/**
	 * Tests if a file exists
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	static public function checkFile($path)
	{
		return file_exists($path);
	}

	/**
	 * Returns file modification time
	 *
	 * @param $path
	 *
	 * @return int
	 */
	static public function fileMTime($path)
	{
		return filemtime($path);
	}

	/**
	 * Down a file from distant server
	 *
	 * @param $url
	 * @param $path
	 *
	 * @return bool
	 */
	static public function downloadFile($url, $path)
	{
		return file_put_contents($path, CurlHelper::get($url)) !== false;
	}

	/**
	 * Returns file size
	 *
	 * @param $path
	 *
	 * @return int
	 */
	static public function fileSize($path)
	{
		return filesize($path);
	}

	/**
	 * Remove a directory and its content
	 *
	 * @param $dir
	 */
	static public function removeDirectory($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);
			foreach ($objects as $object)
			{
				if ($object != "." && $object != "..")
				{
					if (filetype($dir . "/" . $object) == "dir")
						static::removeDirectory($dir . "/" . $object);
					else
						unlink($dir . "/" . $object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	/**
	 * List the content of a directory
	 *
	 * @param $path
	 * @param array $extensions
	 * @param bool $dir
	 * @param bool $dot_file
	 * @param bool $recursive
	 * @param bool $returnFullPath
	 *
	 * @throws DirectoryDoesNotExistsException
	 *
	 * @return array
	 */
	static public function listDirectory($path, $extensions = array(), $dir = true, $dot_file = true, $recursive = false, $returnFullPath = false)
	{
		if (!static::checkFile($path))
			throw(new DirectoryDoesNotExistsException("The specified directory does not exists: " . $path));

		$result = array();
		$filelist = new DirectoryIterator($path);
		foreach ($filelist as $file)
		{
			if ($file->isDot() && $dot_file)
				$result[] = $returnFullPath ? $file->getPathname() : $file->getFilename();
			else if ($file->isDir() && $dir && (($file->isDot() && $dot_file) || !$file->isDot()))
				$result[] = $returnFullPath ? $file->getPathname() : $file->getFilename();
			else if (!$file->isDot() && $file->isDir() && $recursive)
				$result = array_merge($result, self::listDirectory($file->getPathname(), $extensions, $dir, $dot_file, $recursive, $returnFullPath));
			else
			{
				if (sizeof($extensions) > 0)
				{
					foreach ($extensions as $ext)
					{
						if (substr($file->getFilename(), -strlen($ext)) == $ext)
						{
							$result[] = $returnFullPath ? $file->getPathname() : $file->getFilename();
							break;
						}
					}
				} else
					$result[] = $returnFullPath ? $file->getPathname() : $file->getFilename();
			}
		}
		return $result;
	}

	/**
	 * Search for files
	 *
	 * @param string $path
	 * @param string $searchRegEx
	 * @param bool   $recursive
	 *
	 * @throws DirectoryDoesNotExistsException
	 *
	 * @return array
	 */
	static public function search($path, $searchRegEx, $recursive = false)
	{
		if (!static::checkFile($path))
			throw(new DirectoryDoesNotExistsException("The specified directory does not exists: " . $path));

		$result = array();
		$filelist = new DirectoryIterator($path);
		foreach ($filelist as $file)
		{
			if (!$file->isDot() && $file->isDir() && $recursive)
				$result = array_merge($result, self::search($file->getPathname(), $searchRegEx, $recursive));
			else if (preg_match($searchRegEx, $file->getFilename()))
				$result[] = $file->getPathname();
		}
		return $result;
	}

	/**
	 * Deletes a file
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	static public function deleteFile($path)
	{
		if (!FileSystem::checkFile($path))
			return false;
		return unlink($path);
	}

	/**
	 * Create a directory
	 *
	 * @param $path
	 */
	static public function mkdir($path)
	{
		if (!file_exists($path))
			mkdir($path);
	}

	/**
	 * Applies a chmod to a file or directory
	 *
	 * @param $path
	 * @param $mode
	 */
	static public function chmod($path, $mode)
	{
		chmod($path, $mode);
	}

	/**
	 * Reads a file and returns its content
	 *
	 * @param $path
	 *
	 * @return bool|string File content or false is case of failure
	 */
	static public function readFile($path)
	{
		if (!FileSystem::checkFile($path) || !is_readable($path))
			return false;
		return file_get_contents($path);
	}

	/**
	 * Write data into a file
	 *
	 * @param string $path     path of the file to write
	 * @param string $data     data to write into the file
	 * @param bool   $append   If sets to true, the value is added at the end of the file. Else, file content is replaced.
	 *
	 * @return bool Returns true if success, false if failure
	 */
	static public function writeFile($path, $data, $append)
	{
		if (!is_writable(dirname($path)))
			return false;
		file_put_contents($path, $data, $append ? FILE_APPEND : 0);
		return true;
	}

	/**
	 * Checks if a directory is writable
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	static public function isWritable($path)
	{
		return is_writable($path);
	}

	/**
	 * Finds one or many files by browsing one or many directories
	 *
	 * @param $names            Array of possible file names
	 * @param $directories      Array of possible directory names
	 * @param bool $only_once   If set to true, stop when the first occurrence of one of the files is found
	 *
	 * @return array|bool|string Returns one or many paths, or false if no file found.
	 */
	static public function find($names, $directories, $only_once = true)
	{
		$result = $only_once ? '' : array();

		// Browse each directory :
		foreach ($directories as $directory)
		{
			// Browse directory entry:
			$dir = opendir($directory);
			while ($entry = readdir($dir))
			{
				if ($entry != '.' && $entry != '..')
				{
					// If it's a directory, let's go deeper:
					if (is_dir($directory . '/' . $entry))
					{
						if ($only_once)
						{
							$deeper = self::find($names, array($directory . '/' . $entry));
							if ($deeper !== false)
								return $deeper;
						} else
							array_merge($result, find($names, array($directory . '/' . $entry), false));
					} // Else, check for file name:
					else
					{
						// Check each possible file names:
						foreach ($names as $name)
						{
							if ($name === $entry)
							{
								if ($only_once)
									return $directory . '/' . $entry;
								else
									$result[] = $directory . '/' . $entry;
							}
						}
					}
				}
			}
			closedir($dir);
		}

		if ($only_once)
			return false;
		return sizeof($result) === 0 ? false : $result;
	}

}