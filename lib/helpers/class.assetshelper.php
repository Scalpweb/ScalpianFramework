<?php

class AssetsHelper
{

	static protected $js_list = array(), $js_min_list = array();
	static protected $css_list = array(), $css_min_list = array();
	static protected $has_js_listener = false, $has_css_listener = false;

	/**
	 * Add a new javascript file to the js resources list
	 * @param $path
	 * @param bool $absolute_path
	 * @param bool $insert_at_top If set to true, inserts the element at the beginning of the array
	 */
	static public function addJs($path, $absolute_path = false, $insert_at_top = false)
	{
		if (!static::$has_js_listener)
		{
			static::$has_js_listener = true;
			EventHandler::addListener(EventTypes::ORION_BEFORE_DISPLAY, array('AssetsHelper', 'replaceJsAnchors'));
		}

		$path = $absolute_path ? $path : '/js/' . $path;
		if ($insert_at_top)
			array_unshift(static::$js_list, $path);
		else
			static::$js_list[] = $path;
	}

	/**
	 * Add a new javascript file to the js resources list. This js file will be minified.
	 * @param $path
	 * @param bool $insert_at_top If set to true, inserts the element at the beginning of the array
	 */
	static public function addJsToMinifyList($path, $insert_at_top = false)
	{
		if (!static::$has_js_listener)
		{
			static::$has_js_listener = true;
			EventHandler::addListener(EventTypes::ORION_BEFORE_DISPLAY, static::replaceJsAnchors);
		}

		$path = '/js/' . $path;
		if ($insert_at_top)
			array_unshift(static::$js_min_list, $path);
		else
			static::$js_min_list[] = $path;
	}

	/**
	 * Add a new cascading stylesheet file to the css resources list
	 * @param $path
	 * @param string $rel Rel html link tag attribute
	 * @param string $media Media html link tag attribute
	 * @param bool $absolute_path
	 * @param bool $insert_at_top If set to true, inserts the element at the beginning of the array
	 */
	static public function addCss($path, $rel = 'stylesheet', $media = 'all', $absolute_path = false, $insert_at_top = false)
	{
		if (!static::$has_css_listener)
		{
			static::$has_css_listener = true;
			EventHandler::addListener(EventTypes::ORION_BEFORE_DISPLAY, array('AssetsHelper', 'replaceCssAnchors'));
		}

		$path = $absolute_path ? $path : '/css/' . $path;
		if ($insert_at_top)
			array_unshift(static::$css_list, array('path' => $path, 'rel' => $rel, 'media' => $media));
		else
			static::$css_list[] = array('path' => $path, 'rel' => $rel, 'media' => $media);
	}

	/**
	 * Replace js anchors
	 * @param $source
	 */
	static public function replaceJsAnchors($source)
	{
		$code = $source->getHtmlFinalCode();
		$js_code = '';

		if (sizeof(static::$js_min_list) > 0)
		{
			$uuid = static::getJsFileListUID();
			$minify_path = ORION_WEB_DIR . '/assets/' . $uuid . '.js';
			if (!FileSystem::checkFile($minify_path))
			{
				$minify = '';
				foreach (static::$js_min_list as $element)
					$minify .= JSMin::minify(file_get_contents(ORION_WEB_DIR . $element));
				FileSystem::writeFile($minify_path, $minify, false);
			}
			$js_code .= OrionTools::linef('<script type="text/javascript" src="' . $minify_path . '"></script>');
		}
		foreach (static::$js_list as $element)
		{
			$js_code .= OrionTools::linef('<script type="text/javascript" src="' . $element . '"></script>');
		}

		$code = str_replace('<orion js_anchor />', $js_code, $code);
		$source->setHtmlFinalCode($code);
	}

	/**
	 * Add a new cascading stylesheet file  to the css resources list. This css file will be minified.
	 * @param $path
	 * @param string $rel Rel html link tag attribute
	 * @param string $media Media html link tag attribute
	 * @param bool $insert_at_top If set to true, inserts the element at the beginning of the array
	 */
	static public function addCssToMinifyList($path, $rel = 'stylesheet', $media = 'all', $insert_at_top = false)
	{
		if (!static::$has_css_listener)
		{
			static::$has_css_listener = true;
			EventHandler::addListener(EventTypes::ORION_BEFORE_DISPLAY, static::replaceCssAnchors);
		}

		$path = '/css/' . $path;
		if ($insert_at_top)
			array_unshift(static::$css_min_list, array('path' => $path, 'rel' => $rel, 'media' => $media));
		else
			static::$css_min_list[] = array('path' => $path, 'rel' => $rel, 'media' => $media);
	}

	/**
	 * Replace css anchors
	 * @param $source
	 */
	static public function replaceCssAnchors($source)
	{
		$code = Router::getInstance()->getModule()->getHtmlFinalCode();
		$css_code = '';

		if (sizeof(static::$css_min_list) > 0)
		{
			$uuid = static::getCssFileListUID();
			$minify_path = ORION_WEB_DIR . '/assets/' . $uuid . '.css';
			if (!FileSystem::checkFile($minify_path))
			{
				$minify = '';
				foreach (static::$css_min_list as $element)
				{
					$minify .= CssMin::minify(CssImageEncoder::encodeImage(file_get_contents(ORION_WEB_DIR . $element), $element['path']));
				}
				FileSystem::writeFile($minify_path, $minify, false);
			}
			$css_code .= OrionTools::linef('<link type="text/css" href="' . $minify_path . '">');
		}
		foreach (static::$css_list as $element)
		{
			$css_code .= OrionTools::linef('<link type="text/css" rel="' . $element['rel'] . '" href="' . $element['path'] . '" media="' . $element['media'] . '">');
		}

		$code = str_replace('<orion css_anchor />', $css_code, $code);
		Router::getInstance()->getModule()->setHtmlFinalCode($code);
	}

	/**
	 * Echo js files resources tag
	 */
	static public function insertJs()
	{
		echo OrionTools::linef('<orion js_anchor />');
	}

	/**
	 * Echo css files resources tag
	 */
	static public function insertCss()
	{
		echo OrionTools::linef('<orion css_anchor />');
	}

	/**
	 * Returns a unique id for that list, based on last file time modification
	 * @return string
	 */
	static private function getJsFileListUID()
	{
		$list = array();
		foreach (static::$js_list as $path)
			$list[] = $path . '-' . filemtime(ORION_WEB_DIR . $path);
		return md5(implode('-', $list));
	}

	/**
	 * Returns a unique id for that list, based on last file time modification
	 * @return string
	 */
	static private function getCssFileListUID()
	{
		$list = array();
		foreach (static::$css_list as $path)
			$list[] = $path['path'] . '-' . $path['rel'] . '-' . $path['media'] . '-' . filemtime(ORION_WEB_DIR . $path);
		return md5(implode('-', $list));
	}

}