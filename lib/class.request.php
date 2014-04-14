<?php

class Request extends Singleton
{

    /**
     * Checks if a get variable is set
     * @param $ref
     * @return bool
     */
    public function isSetGet($ref)
    {
        return isset($_GET[$ref]);
    }

    /**
     * Checks if a post variable is set
     * @param $ref
     * @return bool
     */
    public function isSetPost($ref)
    {
        return isset($_POST[$ref]);
    }

    /**
     * Set a get variable from URL
     * @param $index
     * @param $value
     */
    public function setGetFromUrl($index, $value)
    {
        $_GET['url'.$index] = $value;
    }

    /**
     * Returns a get variable
     * @param $ref
     * @param bool $clean
     * @throws UndefinedGetVariable
     * @return mixed
     */
    public function getGet($ref, $clean = true)
    {
        if(!isset($_GET[$ref]))
            throw(new UndefinedGetVariable("Unknown get variable: ".$ref));
        return $clean ? htmlentities($_GET[$ref]) : $_GET[$ref];
    }

    /**
     * Returns a post variable
     * @param $ref
     * @param bool $clean
     * @throws UndefinedPostVariable
     * @return mixed
     */
    public function getPost($ref, $clean = true)
    {
        if(!isset($_POST[$ref]))
            throw(new UndefinedPostVariable("Unknown post variable: ".$ref));
        return $clean ? htmlentities($_POST[$ref]) : $_POST[$ref];
    }

	public function getMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public function getUrl()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public function getDomain()
	{
		return $_SERVER['SERVER_NAME'];
	}

	public function getIp()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
	}

	public function getFirstClientIp()
	{
		return isset($_SERVER['HTTP_X_CLIENT_IP']) ? $_SERVER['HTTP_X_CLIENT_IP'] : $this->getIp();
	}

	public function getReferrer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

	public function getQueryString()
	{
		return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
	}

	public function checkIsInternalIp($ip = null, $isWanIncluded = true)
	{
		if (empty($ip)) {
			$ip = $this->getIp();
		}

		if (strpos($ip, '192.168.') === 0) {
			return true;
		}

		return false;
	}

	public function isReferrerOnSameDomain($allowSubdomains = true)
	{
		if (!isset($_SERVER['HTTP_REFERER'])) {
			return false;
		}

		$referrerDomain = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
		if ($referrerDomain === false) {
			return false;
		}

		if ($allowSubdomains) {
			return
				$referrerDomain === $_SERVER['SERVER_NAME'] ||
				stristr($referrerDomain, '.'.$_SERVER['SERVER_NAME']) === '.'.$_SERVER['SERVER_NAME'] ||
				stristr($_SERVER['SERVER_NAME'], '.'.$referrerDomain) === '.'.$referrerDomain;
		}
		else {
			return $referrerDomain === $_SERVER['SERVER_NAME'];
		}
	}

	public function isSecure()
	{
		return !empty($_SERVER['HTTPS']);
	}

	public function isAjaxRequest()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
			// JSON P hack.
			|| (isset($_GET['requestId']) && (isset($_GET['callback']) || isset($_GET['callBack'])))
		;
	}

	public function getUserAgent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	public function getBrowserInfo($userAgent = '')
	{
		if (empty($userAgent)) {
			$userAgent = $this->getUserAgent();
		}
		$userAgent = strtolower($userAgent);

		$info = array(
			'os'      => '?',
			'browser' => '?',
			'version' => '?'
		);

		if (empty($userAgent)) {
			return $info;
		}

		if (strpos($userAgent, 'windows') !== false) {
			$info['os'] = 'win';
		}
		elseif (strpos($userAgent, 'macintosh') !== false) {
			$info['os'] = 'mac';
		}
		elseif (strpos($userAgent, 'linux') !== false) {
			$info['os'] = 'linux';
		}

		$browsers = array(
			'opera'   => array(
				'name'           => 'opera',
				'versionPattern' => '#version/(\d+(?:\.\d+)?)#',
			),
			'msie'    => array(
				'name'           => 'ie',
				'versionPattern' => '#msie (\d+)#',
			),
			'firefox' => array(
				'name'           => 'ff',
				'versionPattern' => '#firefox/(\d+(?:\.\d+)?)#',
			),
			'chrome'  => array(
				'name'           => 'chrome',
				'versionPattern' => '#chrome/(\d+(?:\.\d+)?)#',
			),
		);

		do {
			foreach ($browsers as $browser => $browserData) {
				if (strpos($userAgent, $browser) !== false) {
					$info['browser'] = $browserData['name'];
					$matches = array();
					if (preg_match($browserData['versionPattern'], $userAgent, $matches)) {
						$info['version'] = $matches[1];
					}
					break 2;
				}
			}

			if (strpos($userAgent, 'safari') !== false) {
				$info['browser'] = 'safari';

				if (preg_match('#version/(\d+)#', $userAgent, $matches)) {
					$info['version'] = $matches[1];
				}
				elseif (preg_match('#applewebkit/(\d+)#', $userAgent, $matches)) {
					$safariVersionMatrix = array(
						85  => '1',
						401 => '2',
						521 => '3',
						528 => '4',
						533 => '5'
					);
					$buildVersion = $matches[1];
					$buildVersions = array_keys($safariVersionMatrix);
					$currentBuildVersion = current($buildVersions);
					do {
						if ($currentBuildVersion > $buildVersion) {
							$info['version'] = $safariVersionMatrix[prev($buildVersions)];
							break;
						}
					}
					while (($currentBuildVersion = next($buildVersions)) !== false);
				}
				break;
			}
		}
		while (false);


		return $info;
	}

	public function getAllHeaders()
	{
		if (!strstr(PHP_SAPI, 'apache')) {
			throw new Exception('Request::getAllHeaders() hivas nem Apache kornyezetben!');
		}
		return apache_request_headers();
	}

}