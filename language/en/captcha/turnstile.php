<?php

/**
 * CloudFlare Turnstile extension for phpBB.
 * @author Maxim Maslich <mmaslich@gmail.com>
 * @copyright 2025 Maxim Maslich
 * @license GPL-2.0-only
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * @ignore
 */
if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'CAPTCHA_TURNSTILE' => 'CloudFlare Turnstile',
	'CAPTCHA_TURNSTILE_EXPLAIN' => '',
	'TURNSTILE_KEY' => 'Site key',
	'TURNSTILE_KEY_EXPLAIN' => 'The site key generated on CloudFlare for your domain.',
	'TURNSTILE_SECRET' => 'Secret key',
	'TURNSTILE_SECRET_EXPLAIN' => 'The secret key generated on your CloudFlare account.',
	'TURNSTILE_THEME' => 'Theme',
	'TURNSTILE_THEME_EXPLAIN' => 'The color theme of the CloudFlare Turnstile widget.',
	'TURNSTILE_THEME_LIGHT' => 'Light',
	'TURNSTILE_THEME_DARK' => 'Dark',
	'TURNSTILE_THEME_AUTO' => 'Auto',
	'TURNSTILE_SIZE' => 'Size',
	'TURNSTILE_SIZE_EXPLAIN' => 'The size of the CloudFlare Turnstile widget.',
	'TURNSTILE_SIZE_NORMAL' => 'Normal',
	'TURNSTILE_SIZE_FLEXIBLE' => 'Flexible',
	'TURNSTILE_SIZE_COMPACT' => 'Compact',
	'TURNSTILE_NOT_AVAILABLE' => 'In order to use CloudFlare Turnstile, you must create an account on <a href="https://www.cloudflare.com/" rel="external nofollow noreferrer noopener" target="_blank">www.cloudflare.com</a>.',
	'TURNSTILE_INCORRECT' => 'The solution you provided was incorrect.',
	'TURNSTILE_NOSCRIPT' => 'Please enable JavaScript in your browser to load the challenge.',
	'TURNSTILE_LOGIN_ERROR_ATTEMPTS' => 'You have exceeded the maximum number of login attempts allowed.<br>In addition to your username and password, Turnstile will be used to authenticate your session.',
	'TURNSTILE_REQUEST_EXCEPTION' => 'CloudFlare Turnstile request error: %s',

	'ACP_TURNSTILE_TOGGLE_SECRET' => 'Toggle %s',
	'ACP_TURNSTILE_VALIDATE_INVALID_FIELDS' => 'Invalid values for fields: <samp>%s</samp>'
]);

