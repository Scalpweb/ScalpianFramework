<?php

class ImageHelper
{

	private $imageSource;
	private $borderSize = null;
	private $background = null;
	private $text = null;
	private $refIcon = null;
	private $curThumb = null;
	private $targetDir;
	private $quality;

	function __construct($__src, $__dir, $__quality = 100)
	{
		$this->set_source_src($__src);
		$this->set_save_directory($__dir);
		$this->set_thumbs_quality($__quality);
	}

	// *******************************************************************************
	// --
	// -- Public functions :
	// --
	// *******************************************************************************

	function resize($thumb_width, $thumb_height, $background_color, $filename = null, $stay = false)
	{
		try
		{
			$this->clear_current_thumb();

			$this->curThumb['img'] = imagecreatetruecolor($thumb_width, $thumb_height);
			$this->curThumb['sizes'][0] = $thumb_width;
			$this->curThumb['sizes'][1] = $thumb_height;

			$color = $this->get_rgb_from_hexa($background_color);
			$color = imagecolorallocate($this->curThumb['img'], $color[0], $color[1], $color[2]);
			imagefill($this->curThumb['img'], 0, 0, $color);

			$ratio_source = $this->imageSource['sizes'][0] / $this->imageSource['sizes'][1];
			$ratio_x = $this->imageSource['sizes'][0] / $thumb_width;
			$ratio_y = $this->imageSource['sizes'][1] / $thumb_height;

			$margin_left = 0;
			$margin_top = 0;

			if ($ratio_x > $ratio_y)
			{
				$img_width = $thumb_width;
				$img_height = $thumb_width / $ratio_source;
				$margin_top = $thumb_height / 2 - $img_height / 2;
			} elseif ($ratio_x < $ratio_y)
			{
				$img_height = $thumb_height;
				$img_width = $thumb_height * $ratio_source;
				$margin_left = $thumb_width / 2 - $img_width / 2;
			} else
			{
				$img_width = $thumb_width;
				$img_height = $thumb_height;
			}

			$thumb = imagecreatetruecolor($img_width, $img_height);
			imagecopyresized($thumb, $this->imageSource['img'], 0, 0, 0, 0, $img_width, $img_height, $this->imageSource['sizes'][0], $this->imageSource['sizes'][1]);
			imagecopymerge($this->curThumb['img'], $thumb, $margin_left, $margin_top, 0, 0, $img_width, $img_height, 100);
			if (!is_null($filename))
			{
				return $this->save($filename);
			} else
			{
				return true;
			}
		} catch (Exception $e)
		{
			throw(new Exception("Image cannot be resized to " . $thumb_width . 'x' . $thumb_height));
		}
	}

	function resize_crop($thumb_width, $thumb_height, $filename = null, $stay = false)
	{
		try
		{
			$this->clear_current_thumb();

			$this->curThumb['img'] = imagecreatetruecolor($thumb_width, $thumb_height);
			$this->curThumb['sizes'][0] = $thumb_width;
			$this->curThumb['sizes'][1] = $thumb_height;

			$ratio_x = $this->imageSource['sizes'][0] / $thumb_width;
			$ratio_y = $this->imageSource['sizes'][1] / $thumb_height;

			$mid_height = $thumb_height / 2;
			$mid_width = $thumb_width / 2;

			if ($ratio_x > $ratio_y)
			{
				$final_width = $this->imageSource['sizes'][0] / $ratio_y;
				$int_width = ($final_width / 2) - $mid_width;
				imagecopyresampled($this->curThumb['img'], $this->imageSource['img'], $stay ? 0 : -$int_width, 0, 0, 0, $final_width, $thumb_height, $this->imageSource['sizes'][0], $this->imageSource['sizes'][1]);
			} elseif ($ratio_x < $ratio_y)
			{
				$final_height = $this->imageSource['sizes'][1] / $ratio_x;
				$int_height = ($final_height / 2) - $mid_height;
				imagecopyresampled($this->curThumb['img'], $this->imageSource['img'], 0, $stay ? 0 : -$int_height, 0, 0, $thumb_width, $final_height, $this->imageSource['sizes'][0], $this->imageSource['sizes'][1]);
			} else
			{
				imagecopyresampled($this->curThumb['img'], $this->imageSource['img'], 0, 0, 0, 0, $thumb_width, $thumb_height, $this->imageSource['sizes'][0], $this->imageSource['sizes'][1]);
			}
			if (!is_null($filename))
			{
				return $this->save($filename);
			} else
			{
				return true;
			}
		} catch (Exception $e)
		{
			throw(new Exception("Image cannot be resized to " . $thumb_width . 'x' . $thumb_height));
		}
	}

	function resize_width($thumb_width, $filename = null)
	{
		try
		{
			$this->clear_current_thumb();

			$ratio_x = $this->imageSource['sizes'][0] / $thumb_width;
			$thumb_height = $this->imageSource['sizes'][1] / $ratio_x;

			$this->curThumb['img'] = imagecreatetruecolor($thumb_width, $thumb_height);
			$this->curThumb['sizes'][0] = $thumb_width;
			$this->curThumb['sizes'][1] = $thumb_height;

			imagecopyresampled($this->curThumb['img'], $this->imageSource['img'], 0, 0, 0, 0, $thumb_width, $thumb_height, $this->imageSource['sizes'][0], $this->imageSource['sizes'][1]);
			if (!is_null($filename))
			{
				return $this->save($filename);
			} else
			{
				return true;
			}
		} catch (Exception $e)
		{
			throw(new Exception("Image cannot be resized to width=" . $thumb_width));
		}
	}

	function resize_height($thumb_height, $filename = null)
	{
		try
		{
			$this->clear_current_thumb();

			$ratio_y = $this->imageSource['sizes'][1] / $thumb_height;
			$thumb_width = $this->imageSource['sizes'][0] / $ratio_y;

			$this->curThumb['img'] = imagecreatetruecolor($thumb_width, $thumb_height);
			$this->curThumb['sizes'][0] = $thumb_width;
			$this->curThumb['sizes'][1] = $thumb_height;

			imagecopyresampled($this->curThumb['img'], $this->imageSource['img'], 0, 0, 0, 0, $thumb_width, $thumb_height, $this->imageSource['sizes'][0], $this->imageSource['sizes'][1]);
			if (!is_null($filename))
			{
				return $this->save($filename);
			} else
			{
				return true;
			}
		} catch (Exception $e)
		{
			throw(new Exception("Image cannot be resized to height=" . $thumb_height));
		}
	}

	function save($filename)
	{
		$this->put_icon_if_needed();
		$this->put_text_if_needed();
		return $this->save_thumb_image($this->curThumb['img'], $this->targetDir . $filename, $this->quality);
	}

	function clean()
	{
		@imagedestroy($this->imageSource['img']);
		unset($this->imageSource['img']);
		if (!is_null($this->curThumb))
		{
			@imagedestroy($this->curThumb['img']);
			unset($this->curThumb['img']);
			$this->curThumb = null;
		}
		if (!is_null($this->refIcon))
		{
			@imagedestroy($this->refIcon['img']);
			unset($this->refIcon);
			$this->refIcon = null;
		}
		if (!is_null($this->text))
		{
			@imagedestroy($this->text['img']);
			unset($this->text);
			$this->text = null;
		}
	}

	public function display()
	{
		try
		{
			$type = $this->get_image_extension($this->imageSource['src']);
			header("Content-type: image/" . $type);
			switch ($type)
			{
				case 'gif':
					imagegif($this->curThumb['img']);
					die();
				case 'png':
					imagepng($this->curThumb['img']);
					die();
				case 'jpeg':
				case 'jfif':
				case 'jpg':
				default :
					imagejpeg($this->curThumb['img']);
					die();
					break;
			}
		} catch (Exception $e)
		{
			die($e->__toString() . '. Image Error.');
		}
	}

	// *******************************************************************************
	// --
	// -- Private functions :
	// --
	// *******************************************************************************

	private function build_image_from_path($path)
	{
		try
		{
			$type = $this->get_image_extension($path);
			switch ($type)
			{
				case 'gif':
					return imagecreatefromgif($path);
					break;
				case 'png':
					return imagecreatefrompng($path);
					break;
				case 'jpeg':
				case 'jfif':
				case 'jpg':
				default :
					if (imagecreatefromjpeg($path) !== false)
						return imagecreatefromjpeg($path);
					else
						return imagecreatefrompng($path);
					break;
			}
		} catch (Exception $e)
		{
			throw(new Exception("Impossible to create image from source. Path: " . $path));
		}
	}

	private function save_thumb_image($thumb, $path, $quality)
	{
		try
		{
			$type = $this->get_image_extension($path);
			switch ($type)
			{
				case 'gif':
					imagegif($thumb, $path);
					break;
				case 'png':
					imagepng($thumb, $path);
					break;
				case 'jpeg':
				case 'jpg':
				case 'jfif':
				default :
					imagejpeg($thumb, $path, $quality);
					break;
			}
		} catch (Exception $e)
		{
			throw(new Exception("Impossible to save thumb image at desired location. Path: " . $path));
		}

	}

	private function put_icon_if_needed()
	{
		if (!is_null($this->refIcon))
		{
			$left = 0;
			$top = 0;
			$right = $this->curThumb['sizes'][0] - $this->refIcon['sizes'][0];
			$bottom = $this->curThumb['sizes'][1] - $this->refIcon['sizes'][1];
			$center_left = $right / 2;
			$center_top = $bottom / 2;

			switch ($this->refIcon['position_left'])
			{
				case 'left' :
					$this->refIcon['margin_left'] = $left;
					break;
				case 'right' :
					$this->refIcon['margin_left'] = $right;
					break;
				case 'center' :
					$this->refIcon['margin_left'] = $center_left;
					break;
				default :
					$this->refIcon['margin_left'] = 0;
					break;
			}

			switch ($this->refIcon['position_top'])
			{
				case 'top' :
					$this->refIcon['margin_top'] = $top;
					break;
				case 'bottom' :
					$this->refIcon['margin_top'] = $bottom;
					break;
				case 'center' :
					$this->refIcon['margin_top'] = $center_top;
					break;
				default :
					$this->refIcon['margin_top'] = 0;
					break;
			}

			if (!isset($this->refIcon['is_built']) || !$this->refIcon['is_built'])
			{
				imagealphablending($this->refIcon['img'], false);
				$black = imagecolorallocate($this->refIcon['img'], 0, 0, 0);
				imagefill($this->refIcon['img'], 0, 0, $black);
				imagecolortransparent($this->refIcon['img'], $black);
				$this->refIcon['is_built'] = true;
			}

			$icon_width = $this->refIcon['sizes'][0];
			$icon_height = $this->refIcon['sizes'][1];

			imagecopymerge($this->curThumb['img'], $this->refIcon['img'], $this->refIcon['margin_left'], $this->refIcon['margin_top'], 0, 0, $icon_width, $icon_height, $this->refIcon['alpha']);
			return true;
		} else
		{
			return false;
		}
	}

	private function put_text_if_needed()
	{
		if (!is_null($this->text))
		{
			if (!isset($this->text['color']) || $this->text['color'] == null)
			{
				$this->text['color'] = Array(255, 255, 0);
			}

			$left = 0;
			$top = 0;
			$right = $this->curThumb['sizes'][0] - $this->text['sizes'][0];
			$bottom = $this->curThumb['sizes'][1] - $this->text['sizes'][1];
			$center_left = $right / 2;
			$center_top = $bottom / 2;

			switch ($this->text['position_left'])
			{
				case 'left' :
					$this->text['margin_left'] = $left;
					break;
				case 'right' :
					$this->text['margin_left'] = $right;
					break;
				case 'center' :
					$this->text['margin_left'] = $center_left;
					break;
				default :
					$this->text['margin_left'] = 0;
					break;
			}

			switch ($this->text['position_top'])
			{
				case 'top' :
					$this->text['margin_top'] = $top;
					break;
				case 'bottom' :
					$this->text['margin_top'] = $bottom;
					break;
				case 'center' :
					$this->text['margin_top'] = $center_top;
					break;
				default :
					$this->text['margin_top'] = 0;
					break;
			}

			$color = imagecolorallocate($this->curThumb['img'], $this->text['color'][0], $this->text['color'][1], $this->text['color'][2]);
			$shadow_grey = imagecolorallocate($this->curThumb['img'], 128, 128, 128);

			if (is_int($this->text['font']))
			{
				if (isset($this->text['shadow']) && $this->text['shadow'] == true)
				{
					imagestring($this->curThumb['img'], $this->text['font'], $this->text['margin_left'] + 1, $this->text['margin_top'] + 1, $this->text['text'], $shadow_grey);
				}
				imagestring($this->curThumb['img'], $this->text['font'], $this->text['margin_left'], $this->text['margin_top'], $this->text['text'], $color);
			} else
			{
				if (isset($this->text['shadow']) && $this->text['shadow'] == true)
				{
					imagettftext($this->curThumb['img'], $this->text['size'], 0, $this->text['margin_left'] + 1, $this->text['margin_top'] + 1, $shadow_grey, $font, $this->text['text']);
				}
				imagettftext($this->curThumb['img'], $this->text['size'], 0, $this->text['margin_left'], $this->text['margin_top'], $color, $this->text['font'], $this->text['text']);
			}

			return true;
		} else
		{
			return false;
		}
	}

	private function reload_text_sizes()
	{
		$this->text['sizes'][0] = imagefontwidth($this->text['size']) * strlen($this->text['text']);
		$this->text['sizes'][1] = imagefontheight($this->text['size']) * 1;
	}

	private function clear_current_thumb()
	{
		if ($this->curThumb != null)
		{
			@imagedestroy($this->curThumb['img']);
			if (isset($this->curThumb)) $this->curThumb = null;
			return is_null($this->curThumb);
		} else
		{
			return true;
		}
	}

	// *******************************************************************************
	// --
	// -- Getters and Setters :
	// --
	// *******************************************************************************

	function get_source_path()
	{
		return $this->imageSource['src'];
	}

	function get_source_width()
	{
		return $this->imageSource['sizes'][0];
	}

	function get_source_height()
	{
		return $this->imageSource['sizes'][1];
	}

	function get_save_directory()
	{
		return $this->targetDir;
	}

	function get_current_thumb()
	{
		return $this->curThumb['img'];
	}

	function get_current_thumb_width()
	{
		return $this->curThumb['sizes'][0];
	}

	function get_current_thumb_height()
	{
		return $this->curThumb['sizes'][1];
	}

	static function get_random_string($chars_count = 4)
	{
		$caracteres = "123AaBbCcDdEe456fGgHhIiJjKkLlMmNn789PQqRrSsTtUuVvWwXxYyZz0";
		$chaine = '';
		srand(time());
		for ($i = 0; $i < $chars_count; $i++)
		{
			$chaine .= substr($caracteres, rand() % (strlen($caracteres)), 1);
		}
		return $chaine;
	}

	static function get_image_extension($fn_or_path)
	{
		return strtolower(substr($fn_or_path, strrpos($fn_or_path, '.') + 1));
	}

	static function get_rgb_from_hexa($hex)
	{
		if ($hex[0] == '#')
		{
			$hex = substr($hex, 1);
		}
		$nb_cars = strlen($hex);
		if ($nb_cars == 6)
		{
			list($r, $g, $b) = array($hex[0] . $hex[1], $hex[2] . $hex[3], $hex[4] . $hex[5]);
		} elseif ($nb_cars == 3)
		{
			list($r, $g, $b) = array($hex[0] . $hex[0], $hex[1] . $hex[1], $hex[2] . $hex[2]);
		} else
		{
			return false;
		}
		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);
		return array($r, $g, $b);
	}

	function set_source_src($source_src)
	{
		if (is_file($source_src))
		{
			$this->imageSource['src'] = $source_src;
			$this->imageSource['img'] = $this->build_image_from_path($source_src);
			$this->imageSource['sizes'] = getimagesize($source_src);
			return true;
		} else
		{
			throw(new Exception("The image does not exist. Path: " . $source_src));
		}
	}

	function set_save_directory($save_directory)
	{
		if (is_dir($save_directory))
		{
			$this->targetDir = $save_directory;
			return true;
		} else
		{
			throw(new Exception("This directory does not exist. Path: " . $save_directory));
		}
	}

	function set_thumbs_quality($quality)
	{
		if (intval($quality))
		{
			$this->quality = $quality;
			return true;
		} else
		{
			throw(new Exception("Image quality must be a value between 0 and 100. Value: " . $quality));
		}
	}

	function set_icon($icon_src, $position_top, $position_left, $alpha = null)
	{
		if (is_file($icon_src))
		{
			$this->refIcon['src'] = $icon_src;
			$this->refIcon['sizes'] = getimagesize($icon_src);
			$this->set_icon_position($position_top, $position_left);
			$this->refIcon['img'] = $this->build_image_from_path($icon_src);
			if ($alpha != null) $this->set_icon_alpha($alpha);
			return true;
		} else
		{
			throw(new Exception("This file is not a valid icon file or does not exist. Value: " . $icon_src));
		}
	}

	function set_icon_alpha($alpha)
	{
		if (intval($alpha))
		{
			$this->refIcon['alpha'] = $alpha;
			return true;
		} else
		{
			throw(new Exception("Icon alpha opacity must be a value between 0 and 100. Value: " . $alpha));
		}
	}

	function set_icon_position($position_top, $position_left)
	{
		$allowed_positions = Array('top', 'bottom', 'left', 'right', 'center');
		if (in_array($position_top, $allowed_positions))
		{
			$this->refIcon['position_top'] = $position_top;
			if (in_array($position_left, $allowed_positions))
			{
				$this->refIcon['position_left'] = $position_left;
				return true;
			} else
			{
				throw(new Exception('Position must be one of the following: "top", "bottom", "left", "right" and "center"'));
			}
		} else
		{
			throw(new Exception('Position must be one of the following: "top", "bottom", "left", "right" and "center"'));
		}
	}

	function set_icon_autoresize($bool)
	{
		if (!is_bool($bool))
			throw(new Exception("Argument must be a boolean."));
		$this->refIcon['autoresize'] = $bool;
	}

	function set_text($text, $position_top, $position_left, $hex_color, $size = 20)
	{
		if ($text != null)
		{
			$this->text['text'] = $text;
			$allowed_positions = Array('top', 'bottom', 'left', 'right', 'center');
			if (in_array($position_top, $allowed_positions))
			{
				$this->text['position_top'] = $position_top;
				if (in_array($position_left, $allowed_positions))
				{
					$this->text['position_left'] = $position_left;
					$this->set_text_size($size);
					$this->set_text_font(3);
					$this->set_text_color($hex_color);
					$this->reload_text_sizes();
					return true;
				} else
				{
					throw(new Exception('Position must be one of the following: "top", "bottom", "left", "right" and "center"'));
				}
			} else
			{
				throw(new Exception('Position must be one of the following: "top", "bottom", "left", "right" and "center"'));
			}
		}
	}

	function set_text_color($hex)
	{
		$this->text['color'] = $this->get_rgb_from_hexa($hex);
	}

	function set_text_font($font)
	{
		$this->text['font'] = $font;
		$this->reload_text_sizes();
	}

	function set_text_size($size)
	{
		if (is_int($size))
		{
			$this->text['size'] = $size;
			$this->reload_text_sizes();
		}

	}

	function set_text_autoresize($bool)
	{
		if (!is_bool($bool))
			throw(new Exception("Argument must be a boolean."));
		$this->text['autoresize'] = $bool;
	}

	function set_text_shadow($bool)
	{
		if (!is_bool($bool))
			throw(new Exception("Argument must be a boolean."));
		$this->text['shadow'] = $bool;
	}

	function remove_icon()
	{
		$this->refIcon = null;
	}

	function remove_text()
	{
		$this->text = null;
	}
}