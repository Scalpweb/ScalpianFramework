<?php

class CurlHelper
{

	private $url, $return_result, $options = array(), $result_to_file_path = '', $result_to_file_mode = '', $curlInstance;

	public function __construct($_url, $_return_result = true)
	{
		$this->url = $_url;
		$this->return_result = $_return_result;
		$this->curlInstance = curl_init($this->url);
	}

	/**
	 * Call a local action
	 *
	 * @param $appName
	 * @param $moduleName
	 * @param $actionName
	 * @param $data
	 * @param bool $isPost
	 */
	public static function callAction($appName, $moduleName, $actionName, $data = array(), $isPost = false)
	{
		$url = Request::getInstance()->isSecure() ? 'https://' : 'http://';
		$url .= Request::getInstance()->getDomain() . '/';
		$url .= $appName . '/' . $moduleName . '/' . $actionName;
		if ($isPost)
			self::post($url, $data);
		else
			self::get($url, $data);
	}

	/**
	 * @param $url
	 * @param $data
	 * @return bool|mixed
	 */
	public static function post($url, $data = array())
	{
		$curl = new CurlHelper($url, true);
		$curl->setPostData($data);
		$result = $curl->execute();
		return $result;
	}

	/**
	 * @param $url
	 * @param $data
	 * @return bool|mixed
	 */
	public static function get($url, $data = array())
	{
		if (sizeof($data) > 0)
		{
			$url .= '?';
			foreach ($data as $k => $v)
				$url .= $k . '=' . $v . '&';
		}
		$curl = new CurlHelper($url, true);
		$result = $curl->execute();
		return $result;
	}

	/**
	 * @param $datas
	 */
	public function setPostData($datas)
	{
		$this->setOption(CURLOPT_POST, is_array($datas) ? sizeof($datas) : true);
		$this->setOption(CURLOPT_POSTFIELDS, $datas);
	}

	/**
	 * @param $path
	 * @param string $mode
	 */
	public function writeResultToFile($path, $mode = 'w')
	{
		$this->result_to_file_path = $path;
		$this->result_to_file_mode = $mode;
	}

	/**
	 * @param $option
	 * @param $value
	 */
	public function setOption($option, $value)
	{
		$this->options[$option] = $value;
	}

	/**
	 * Execute curl
	 * @return bool|mixed
	 */
	public function execute()
	{

		if ($this->result_to_file_path !== '')
		{
			$fp = fopen($this->result_to_file_path, $this->result_to_file_mode);
			curl_setopt($this->curlInstance, CURLOPT_FILE, $fp);
		}

		if($this->return_result)
			curl_setopt($this->curlInstance, CURLOPT_RETURNTRANSFER, true);

		foreach ($this->options as $key => $value)
			curl_setopt($this->curlInstance, $key, $value);

		Logger::getInstance()->log(LoggerEntry::CURL, 'CurlHelper', 'Send request to ' . $this->url.', with options: <pre>'.OrionTools::print_r($this->options, 0, true).'</pre>');
		$result = curl_exec($this->curlInstance);
		Logger::getInstance()->log(LoggerEntry::CURL, 'CurlHelper', 'Request ended: '.OrionTools::print_r($result, 0, true));
		curl_close($this->curlInstance);

		if ($this->result_to_file_path !== '')
		{
			fclose($fp);
		}

		return $this->return_result ? $result : true;
	}

	/**
	 * @return resource
	 */
	public function getCurlObject()
	{
		return $this->curlInstance;
	}

	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
	}

}