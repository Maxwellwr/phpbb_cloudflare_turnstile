<?php

/**
 * CloudFlare Turnstile extension for phpBB.
 * @author Maxim Maslich <mmaslich@gmail.com>
 * @copyright 2025 Maxim Maslich
 * @license GPL-2.0-only
 */

namespace maxwellwr\turnstile\migrations\v10x;

use phpbb\db\migration\migration;

class m001_config extends migration
{
	/**
	 * Add Turnstile configuration.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			[
				'config.add',
				['turnstile_key', '']
			],
			[
				'config.add',
				['turnstile_secret', '']
			],
			[
				'config.add',
				['turnstile_theme', '']
			],
			[
				'config.add',
				['turnstile_size', '']
			],
		];
	}
}
