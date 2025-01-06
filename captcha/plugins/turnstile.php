<?php

/**
 * CloudFlare Turnstile extension for phpBB.
 * @author Maxim Maslich <mmaslich@gmail.com>
 * @copyright 2025 Maxim Maslich
 * @license GPL-2.0-only
 */

namespace maxwellwr\turnstile\captcha\plugins;

use phpbb\captcha\plugins\captcha_abstract;
use phpbb\config\config;
use phpbb\user;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\log\log;
use maxwellwr\turnstile\includes\helper;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class turnstile extends captcha_abstract
{
	/** @var config */
	protected $config;

	/** @var user */
	protected $user;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var helper */
	protected $helper;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor of Turnstile plugin.
	 *
	 * @param config	$config
	 * @param user		$user
	 * @param request	$request
	 * @param template	$template
	 * @param language	$language
	 * @param log		$log
	 * @param helper	$helper
	 * @param string	$root_path
	 * @param string	$php_ext
	 *
	 * @return void
	 */
	public function __construct(config $config, user $user, request $request, template $template, language $language, log $log, helper $helper, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->template = $template;
		$this->language = $language;
		$this->log = $log;
		$this->helper = $helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Initialize this CAPTCHA plugin.
	 *
	 * @param integer $type
	 *
	 * @return void
	 */
	public function init($type)
	{
		$this->language->add_lang('captcha/turnstile', 'maxwellwr/turnstile');
		parent::init($type);
	}

	/**
	 * Not needed.
	 *
	 * @return void
	 */
	public function execute()
	{
	}

	/**
	 * Not needed.
	 *
	 * @return void
	 */
	public function execute_demo()
	{
	}

	/**
	 * Not needed.
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function get_generator_class()
	{
		throw new \Exception('No generator class given.');
	}

	/**
	 * Get CAPTCHA plugin name.
	 *
	 * @return string
	 */
	public function get_name()
	{
		return 'CAPTCHA_TURNSTILE';
	}

	/**
	 * Indicator that this CAPTCHA plugin requires configuration.
	 *
	 * @return bool
	 */
	public function has_config()
	{
		return true;
	}

	/**
	 * Whether or not this CAPTCHA plugin is available and setup.
	 *
	 * @return bool
	 */
	public function is_available()
	{
		$this->language->add_lang('captcha/turnstile', 'maxwellwr/turnstile');
		return (!empty($this->config['turnstile_key']) && !empty($this->config['turnstile_secret']));
	}

	/**
	 * Create the ACP page for configuring this CAPTCHA plugin.
	 *
	 * @param string		$id
	 * @param \acp_captcha	$module
	 *
	 * @return void
	 */
	public function acp_page($id, $module)
	{
		$module->tpl_name = '@maxwellwr_turnstile/acp_captcha_turnstile';
		$module->page_title = 'ACP_VC_SETTINGS';

		$form_key = 'acp_captcha';
		add_form_key($form_key);

		// Allowed values
		$allowed = [
			'theme'	=> ['light', 'dark', 'auto'],
			'size'	=> ['normal', 'flexible', 'compact']
		];

		// Validation errors
		$errors = [];

		// Field filters
		$filters = [
			'turnstile_key' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^0x[a-fA-F0-9]{8}[a-zA-Z0-9]{10}\-[a-z0-9]{3}$#'
				]
			],
			'turnstile_secret' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^0x[a-fA-F0-9]{8}[a-zA-Z0-9]{6}\-[a-zA-Z0-9]{7}_[a-zA-Z0-9]{10}$#'
				]
			],
			'turnstile_theme' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^(?:' . implode('|', $allowed['theme']) . ')?$#'
				]
			],
			'turnstile_size' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^(?:' . implode('|', $allowed['size']) . ')?$#'
				]
			]
		];

		// Request form data
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($module->u_action), E_USER_WARNING);
			}

			// Form data
			$fields = [
				'turnstile_key' => $this->request->variable('turnstile_key', ''),
				'turnstile_secret' => $this->request->variable('turnstile_secret', ''),
				'turnstile_theme' => $this->request->variable('turnstile_theme', ''),
				'turnstile_size' => $this->request->variable('turnstile_size', '')
			];

			// Validation check
			if ($this->helper->validate($fields, $filters, $errors))
			{
				// Save configuration
				foreach ($fields as $key => $value)
				{
					$this->config->set($key, $value);
				}

				// Admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_VISUAL');

				// Confirm dialog
				trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($module->u_action));
			}
		}

		// Assign template variables
		$this->template->assign_vars([
			'U_ACTION'			=> $module->u_action,

			'CAPTCHA_NAME'		=> $this->get_service_name(),
			'CAPTCHA_PREVIEW'	=> $this->get_demo_template($id),

			'TURNSTILE_KEY'		=> $this->config['turnstile_key'],
			'TURNSTILE_SECRET'	=> $this->config['turnstile_secret'],

			'S_TURNSTILE_SETTINGS' => true
		]);

		// Assign allowed values
		foreach ($allowed as $key => $value)
		{
			$block_var = sprintf('TURNSTILE_%s_LIST', strtoupper($key));

			foreach ($value as $val)
			{
				$this->template->assign_block_vars($block_var, [
					'KEY' => $val,
					'NAME' => $this->language->lang(sprintf(
						'TURNSTILE_%1$s_%2$s',
						strtoupper($key),
						strtoupper($val)
					)),
					'ENABLED' => ($this->config[sprintf('turnstile_%s', $key)] === $val)
				]);
			}
		}

		// Assign validation errors
		foreach ($errors as $error)
		{
			$this->template->assign_block_vars('VALIDATION_ERRORS', [
				'MESSAGE' => $error['message']
			]);
		}
	}

	/**
	 * Create the ACP page for previewing this CAPTCHA plugin.
	 *
	 * @see get_template()
	 *
	 * @param string $id
	 *
	 * @return bool|string
	 */
	public function get_demo_template($id)
	{
		return $this->get_template();
	}

	/**
	 * Get the template for this CAPTCHA plugin.
	 *
	 * @return bool|string False if CAPTCHA is already solved, template file name otherwise
	 */
	public function get_template()
	{
		if ($this->is_solved())
		{
			return false;
		}

		$contact = phpbb_get_board_contact_link($this->config, $this->root_path, $this->php_ext);
		$explain = $this->type !== CONFIRM_POST ? 'CONFIRM_EXPLAIN' : 'POST_CONFIRM_EXPLAIN';

		$this->template->assign_vars([
			'CONFIRM_EXPLAIN'		=> $this->language->lang($explain, '<a href="' . $contact . '">', '</a>'),

			'TURNSTILE_KEY'			=> $this->config['turnstile_key'],
			'TURNSTILE_THEME'		=> $this->config['turnstile_theme'],
			'TURNSTILE_SIZE'			=> $this->config['turnstile_size'],
			'U_TURNSTILE_SCRIPT'		=> 'https://challenges.cloudflare.com/turnstile/v0/api.js',
			'S_TURNSTILE_AVAILABLE'	=> $this->is_available(),

			'S_CONFIRM_CODE'		=> true,
			'S_TYPE'				=> $this->type
		]);

		return '@maxwellwr_turnstile/captcha_turnstile.html';
	}

	/**
	 * Validate the user's input.
	 *
	 * @see turnstile_verify_token()
	 *
	 * @return bool|string
	 */
	public function validate()
	{
		if (!parent::validate())
		{
			return false;
		}

		return $this->turnstile_verify_token();
	}

	/**
	 * Validate the token returned by Turnstile.
	 *
	 * @return bool|string False on success, string containing the error otherwise
	 */
	protected function turnstile_verify_token()
	{
		$result = $this->request->variable('cf-turnstile-response', '', true);

		if (empty($result))
		{
			return $this->language->lang('TURNSTILE_INCORRECT');
		}

		// Verify Turnstile token
		try
		{
			$client = new GuzzleClient([
				'base_uri' => 'https://challenges.cloudflare.com',
				'allow_redirects' => false
			]);

			$response = $client->request('POST', '/turnstile/v0/siteverify', [
				'form_params' => [
					'secret'	=> $this->config['turnstile_secret'],
					'response'	=> $result,
					'remoteip'	=> $this->request->server('HTTP_CF_CONNECTING_IP')
				]
			]);

			$data = json_decode($response->getBody()->getContents());

			if ($data->success === true)
			{
				$this->solved = true;
				return false;
			}
		}
		catch (GuzzleException $ex)
		{
			return $this->language->lang('TURNSTILE_REQUEST_EXCEPTION', $ex->getMessage());
		};

		return $this->language->lang('TURNSTILE_INCORRECT');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_login_error_attempts(): string
	{
		$this->language->add_lang('captcha/turnstile', 'maxwellwr/turnstile');
		return 'TURNSTILE_LOGIN_ERROR_ATTEMPTS';
	}
}
