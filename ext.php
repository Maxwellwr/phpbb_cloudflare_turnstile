<?php

/**
 * CloudFlare Turnstile extension for phpBB.
 * @author Maxim Maslich <mmaslich@gmail.com>
 * @copyright 2025 Maxim Maslich
 * @license GPL-2.0-only
 */

namespace maxwellwr\turnstile;

use phpbb\extension\base;

class ext extends base
{
	/**
	 * Check whether or not the extension can be enabled.
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=');
	}

	/**
	 * Handle configuration values when enabling the extension.
	 *
	 * @param mixed $old_state
	 *
	 * @return bool
	 */
	public function enable_step($old_state)
	{
		$parent_state = parent::enable_step($old_state);

		if ($parent_state === false)
		{
			$this->handle_turnstile('enable');
		}

		return $parent_state;
	}

	/**
	 * Handle configuration values when disabling the extension.
	 *
	 * @param mixed $old_state
	 *
	 * @return bool
	 */
	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$state = $this->handle_turnstile('disable');
				break;

			default:
				$state = parent::disable_step($old_state);
				break;
		}

		return $state;
	}

	/**
	 * Handle configuration values when purging the extension.
	 *
	 * @param mixed $old_state
	 *
	 * @return bool
	 */
	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$state = $this->handle_turnstile('purge');
				break;

			default:
				$state = parent::purge_step($old_state);
				break;
		}

		return $state;
	}

	/**
	 * Turnstile step configuration handler.
	 *
	 * @param string $step The name of the step.
	 *
	 * @return bool|string
	 */
	private function handle_turnstile($step = '')
	{
		if (empty($step))
		{
			return false;
		}

		$config = $this->container->get('config');
		$fallback_service = 'core.captcha.plugins.gd';
		$turnstile_service = 'maxwellwr.turnstile.captcha.turnstile';

		switch ($step)
		{
			case 'enable':
				$old_captcha = !empty($config['old_captcha_plugin']) ? $config['old_captcha_plugin'] : $fallback_service;
				$current_captcha = $config['captcha_plugin'];

				$config->set('old_captcha_plugin', $current_captcha);

				if ($old_captcha === $turnstile_service)
				{
					$config->set('captcha_plugin', $turnstile_service);
				}
			break;

			case 'disable':
				$old_captcha = !empty($config['old_captcha_plugin']) ? $config['old_captcha_plugin'] : $fallback_service;
				$old_captcha = ($old_captcha !== $turnstile_service) ? $old_captcha : $fallback_service;
				$current_captcha = $config['captcha_plugin'];

				$config->set('old_captcha_plugin', $current_captcha);

				if ($current_captcha === $turnstile_service)
				{
					$config->set('captcha_plugin', $old_captcha);
				}
			break;

			case 'purge':
				$config->delete('old_captcha_plugin');
			break;
		}

		return 'turnstile_handled';
	}
}
