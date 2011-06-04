<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;



class Email {

	/**
	 * @var  Email_Driver  Keeps an instance to work with for static usage, is reset after send
	 */
	protected static $_instance = null;

	/**
	 * @var  string  debug info from the last email send through static usage
	 */
	protected static $_debug = null;

	/**
	 * Returns the current instance for static usage or creates a new one when empty
	 *
	 * @return  Email_Driver
	 */
	public static function instance()
	{
		if (static::$_instance == null)
		{
			static::$_instance = static::factory();
		}

		return static::$_instance;
	}

	/**
	 * Creates a new instance of the email driver
	 *
	 * @param   array  $config
	 * @return  Email_Driver
	 */
	public static function factory(array $config = array())
	{
		$defaults = Config::load('email', array());

		$config   = $config + $defaults;
		$protocol = ! empty($config['protocol']) ? $config['protocol'] : 'mail';
		$class    = 'Email_'.\Str::ucfirst($protocol);
		if ($protocol == 'Driver' || ! class_exists($class))
		{
			throw new \RuntimeException('Protocol '.$protocol.' is not a valid protocol for emailing.');
		}

		return $class::factory($config);
	}

	/**
	 * Add a header to the email
	 *
	 * @param   string  header name
	 * @param   string  header value
	 * @param   bool    whether to overwrite a current one when already set
	 * @return  Email_Driver|array
	 */
	public static function add_header()
	{
		return call_user_func_array(array(static::instance(), 'add_header'), func_get_args());
	}

	/**
	 * Adds a direct recipient, no args to fetch current ones
	 *
	 * @param   string|array  recipient email or array(name, email)
	 * @param   string|array  ... another recipients to add, etc...
	 * @return  Email_Driver|array
	 */
	public static function to()
	{
		return call_user_func_array(array(static::instance(), 'to'), func_get_args());
	}

	/**
	 * Adds a cc recipient, no args to fetch current ones
	 *
	 * @param   string|array  recipient email or array(name, email)
	 * @param   string|array  ... another recipients to add, etc...
	 * @return  Email_Driver|array
	 */
	public static function cc()
	{
		return call_user_func_array(array(static::instance(), 'cc'), func_get_args());
	}

	/**
	 * Adds a bcc recipient, no args to fetch current ones
	 *
	 * @param   string|array  recipient email or array(name, email)
	 * @param   string|array  ... another recipients to add, etc...
	 * @return  Email_Driver|array
	 */
	public static function bcc()
	{
		return call_user_func_array(array(static::instance(), 'bcc'), func_get_args());
	}

	/**
	 * Sets the sender, no args to fetch current one
	 *
	 * @param   string       The email address of the sender or name (when name: address must be second)
	 * @param   string|null  Emailaddress of the sender when first arg was name
	 * @return  Email_Driver|string
	 */
	public static function from()
	{
		return call_user_func_array(array(static::instance(), 'from'), func_get_args());
	}

	/**
	 * Set the subject, no args to fetch current one
	 *
	 * @param   string  the subject
	 * @return  Email_Driver|string
	 */
	public static function subject()
	{
		return call_user_func_array(array(static::instance(), 'subject'), func_get_args());
	}

	/**
	 * Set the content, no args to fetch current ones
	 *
	 * @param   string  the content
	 * @return  Email_Driver
	 */
	public static function content()
	{
		return call_user_func_array(array(static::instance(), 'content'), func_get_args());
	}

	/**
	 * Add string to the content
	 *
	 * @param   string  the content to add
	 * @return  Email_Driver
	 */
	public static function add_content($content)
	{
		return static::instance()->add_content($content);
	}

	/**
	 * Set the alternative content, no args to fetch current ones
	 *
	 * @param   string  the alt content
	 * @return  Email_Driver
	 */
	public static function alt_content($content)
	{
		return call_user_func_array(array(static::instance(), 'alt_content'), func_get_args());
	}

	/**
	 * Add string to the alt content
	 *
	 * @param   string  the content to add
	 * @return  Email_Driver
	 */
	public static function add_alt_content($content)
	{
		return static::instance()->add_alt_content($content);
	}

	/**
	 * Sends the email.
	 *
	 * @return  bool  success
	 */
	public static function send()
	{
		$email = static::instance();
		static::$_instance = null;
		static::$_debug    = $email->debug();

		return $email->send();
	}

	/**
	 * Attaches a file in the local filesystem to the email
	 *
	 * @param   string  The file to be used
	 * @return  Email_Driver
	 */
	public static function attach($filename)
	{
		return static::instance()->attach($filename);
	}

	/**
	 * Returns the debugging string
	 *
	 * @return  string|null
	 */
	public static function debug()
	{
		return static::$_debug;
	}
}

// end of file email.php