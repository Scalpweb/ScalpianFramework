<?php

class Block
{

	private $_block_path, $module;

	public function __construct($module, $blockName)
	{
		$this->module = $module;
		$blockName = explode('/', $blockName);
		$name = $blockName[sizeof($blockName) - 1];
		$dirs = '';
		for ($i = 0; $i < sizeof($blockName) - 1; $i++)
			$dirs .= $blockName[$i] . '/';

		// Can the block be read ?
		$blockPath = ORION_APP_DIR . '/' . strtolower($module->getApplication()->getName()) . '/blocks/' . $dirs . 'block.' . $name . '.php';
		if (!FileSystem::checkFile($blockPath))
			throw(new BlockDoesNotExistException("The block [" . $name . "] does not exist or can't be read. Path: " . $blockPath));

		$this->_block_path = $blockPath;
	}

	public function includeBlock($arguments)
	{
		foreach ($arguments as $key => $value)
		{
			if ($key === '_block_path')
				throw(new Exception('Argument name is not valid: _block_path is a reserved word.'));
			$this->$key = $value;
		}

		// Include block
		include($this->_block_path);
	}

	public function getModule()
	{
		return $this->module;
	}

}