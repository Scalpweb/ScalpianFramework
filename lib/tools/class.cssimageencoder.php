<?php

class CssImageEncoder
{

	static public function encodeImage($content, $path)
	{
		$dir = dirname($path);
		$content = str_replace('url(', '<?php ___url("' . ($dir != '' && $dir != '.' ? '/css/' . $dir . '/' : '') . '", "', $content);
		$content = str_replace(')', '")?>', $content);

		ob_start();
		eval('?>' . $content);
		$content = ob_get_contents() . "\n";
		ob_end_clean();

		$content = str_replace('")?>', ')', $content);
		return $content;
	}

}