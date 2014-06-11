<?php

class SecurityHandler extends Singleton
{

	/**
	 * Return attempt history for a user
	 *
	 * @param string $attemptIdentifier
	 * @param string $ip
	 * @return array
	 */
	public function getHistoryForIp($attemptIdentifier, $ip)
	{
		$history = array();
		$history['ban'] = Cache::getInstance()->get($attemptIdentifier . '.history.ban.' . $ip);
		$history['last'] = Cache::getInstance()->get($attemptIdentifier . '.history.last.' . $ip);
		$history['count'] = Cache::getInstance()->get($attemptIdentifier . '.history.count.' . $ip);
		return $history;
	}

	/**
	 * Checks if the current user can execute an action
	 *
	 * @param string $attemptIdentifier
	 * @param int $historyTtl
	 * @param int $historyLimit
	 * @param int $banDuration
	 * @return bool
	 */
	public function canProcess($attemptIdentifier, $historyTtl = 60, $historyLimit = 10, $banDuration = 600)
	{
		$ip = Request::getInstance()->getIp();

		// -- If user is requesting from whitelisted ip, allow access:
		$whitelist = Variables::get('ip.whitelist');
		if (is_array($whitelist) && in_array($ip, $whitelist))
			return true;

		// -- Tests if the user is currently banned:
		$bannedUntil = Cache::getInstance()->get($attemptIdentifier . '.history.ban.' . $ip);
		if ($bannedUntil !== false)
		{
			if ($bannedUntil > time())
				return false;
			else
				Cache::getInstance()->delete($attemptIdentifier . '.history.ban.' . $ip);
		}

		// -- Tests if the user did too many recent download:
		$lastDownload = Cache::getInstance()->get($attemptIdentifier . '.history.last.' . $ip);
		$recentCount = Cache::getInstance()->get($attemptIdentifier . '.history.count.' . $ip);
		if ($lastDownload > time() - $historyTtl && $recentCount >= $historyLimit)
		{
			// -- Ban user:
			Cache::getInstance()->set($attemptIdentifier . '.history.ban.' . $ip, time() + $banDuration);
			return false;
		}

		return true;
	}

	/**
	 * Record a user download attempt
	 *
	 * @param string $attemptIdentifier
	 * @param int $historyTtl
	 */
	public function recordAttempt($attemptIdentifier, $historyTtl = 60)
	{
		$ip = Request::getInstance()->getIp();

		$lastDownload = Cache::getInstance()->get($attemptIdentifier . '.history.last.' . $ip);
		if ($lastDownload === false || $lastDownload < time() - $historyTtl)
			Cache::getInstance()->delete($attemptIdentifier . '.history.count.' . $ip);

		// -- Set history timestamp:
		Cache::getInstance()->set($attemptIdentifier . '.history.last.' . $ip, time());

		// -- Increment history count:
		$recentCount = Cache::getInstance()->get($attemptIdentifier . '.history.count.' . $ip);
		if ($recentCount === false)
			$recentCount = 0;
		Cache::getInstance()->set($attemptIdentifier . '.history.count.' . $ip, $recentCount + 1);
	}

	/**
	 * Reset a user attempt history
	 *
	 * @param string $attemptIdentifier
	 */
	public function resetAttempt($attemptIdentifier)
	{
		$ip = Request::getInstance()->getIp();
		Cache::getInstance()->delete($attemptIdentifier . '.history.last.' . $ip);
		Cache::getInstance()->delete($attemptIdentifier . '.history.count.' . $ip);
		Cache::getInstance()->delete($attemptIdentifier . '.history.ban.' . $ip);
	}

}