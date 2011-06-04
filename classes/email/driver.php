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

abstract class Email_Driver {

	public static function factory(array $config = array())
	{
		return new static($config);
	}

	/**
	 * @var  array  An array of headers for the email.
	 */
	protected $headers = array();

	/**
	 * @var  array  An array of all recipients to add in the To: header
	 */
	protected $to = array();

	/**
	 * @var  array  An array of all recipients to add in the CC: header
	 */
	protected $cc = array();

	/**
	 * @var  array  An array of all recipients to add in the BCC: header
	 */
	protected $bcc = array();

	/**
	 * @var  string  The email address of the email sender.
	 */
	protected $from = '';

	/**
	 * @var  string  The subject of the email.
	 */
	protected $subject = '';

	/**
	 * @var  string  The html contents of the email.
	 */
	protected $content = '';

	/**
	 * @var  string  The plain text contents of the email.
	 */
	protected $alt_content = '';

	/**
	 * @var  array  An array of filesystem and dynamic attachments.
	 */
	protected $attachments = array();

	/**
	 * @var  integer  The priority of the email. 1-5 are acceptable.
	 */
	protected $priority = 3;

	/**
	 * @var  string  The charset of the email, null means use Fuel default
	 */
	protected $charset = null;

	/**
	 * @var  string  The useragent of the email, placed in both
	 */
	protected $useragent = 'Fuel PHP Framework';

	/**
	 * @var  string  The content MIME type
	 */
	protected $content_type = 'text/html';

	/**
	 * @var  string  New line character. \r\n according to specs, but \n for compatability.
	 */
	protected $newline = "\n";

	/**
	 * @var  string  Used to set wordwrap on or off
	 */
	protected $wordwrap = true;

	/**
	 * @var  integer  How many characters are allowed a line with wordwrapping.
	 */
	protected $wordwrap_width = 76;

	public function __construct(array $config = array())
	{
		$this->set_config($config);
	}

	/**
	 * Add a header to the email, no args for current ones
	 *
	 * @param   string  header name
	 * @param   string  header value
	 * @param   bool    whether to overwrite a current one when already set
	 * @return  Email_Driver|array
	 */
	public function add_header($index = null, $value = null, $override = true)
	{
		if (is_null($index))
		{
			return $this->headers;
		}

		if (($override or empty($this->headers[$index])) and ! empty($index) && ! empty($value))
		{
			$this->headers[$index] = $value;
		}

		return $this;
	}

	/**
	 * Adds a recipient to the email.
	 *
	 * @param  string  recipient type
	 * @param  array   email addresses
	 */
	protected function add_recipient($type, $input)
	{
		foreach ($input as $email)
		{
			if (is_array($email))
			{
				if ( ! filter_var($email[1], FILTER_VALIDATE_EMAIL))
				{
					throw new \InvalidArgumentException('The following emailaddress is invalid: '.$email[1]);
				}
				$this->{$type}[$email[1]] = $email[0];
			}
			else
			{
				if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					throw new \InvalidArgumentException('The following emailaddress is invalid: '.$email);
				}
				$this->{$type}[$email] = null;
			}
		}
	}

	/**
	 * Adds a direct recipient, no args to fetch current ones
	 *
	 * @param   string|array  recipient email or array(name, email)
	 * @param   string|array  ... another recipients to add, etc...
	 * @return  Email_Driver|array
	 */
	public function to()
	{
		$args = func_get_args();

		if (empty($args))
		{
			return $this->to;
		}

		$this->add_recipient('to', $args);
		return $this;
	}

	/**
	 * Adds a cc recipient, no args to fetch current ones
	 *
	 * @param   string|array  recipient email or array(name, email)
	 * @param   string|array  ... another recipients to add, etc...
	 * @return  Email_Driver|array
	 */
	public function cc()
	{
		$args = func_get_args();

		if (empty($args))
		{
			return $this->cc;
		}

		$this->add_recipient('cc', $args);
		return $this;
	}

	/**
	 * Adds a bcc recipient, no args to fetch current ones
	 *
	 * @param   string|array  recipient email or array(name, email)
	 * @param   string|array  ... another recipients to add, etc...
	 * @return  Email_Driver|array
	 */
	public function bcc()
	{
		$args = func_get_args();

		if (empty($args))
		{
			return $this->to;
		}

		$this->add_recipient('bcc', $args);
		return $this;
	}

	/**
	 * Sets the senders email address
	 *
	 * @param   string       The email address of the sender or name (when name: address must be second)
	 * @param   string|null  Emailaddress of the sender when first arg was name
	 * @return  Email_Driver|string
	 */
	public function from($name = null, $email = null)
	{
		if (is_null($name))
		{
			return $this->from;
		}

		if (is_null($email) and is_array($name))
		{
			$email = $name[1];
			$name = $name[0];
		}
		elseif (is_null($email))
		{
			$email = $name;
			$name = null;
		}

		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			throw new \InvalidArgumentException('The following emailaddress is invalid: '.$email);
		}

		$name and $this->from = '"'.str_replace('"', '&quot;', $name).'"';
		$this->from .= '<'.$email.'>';

		return $this;
	}

	/**
	 * Set the subject, no args to fetch current one
	 *
	 * @param   string  the subject
	 * @return  Email_Driver|string
	 */
	public function subject($subject = null)
	{
		if (is_null($subject))
		{
			return $this->subject;
		}

		$this->subject = $subject;
		return $this;
	}

	/**
	 * Set the content, no args to fetch current one
	 *
	 * @param   string  the content
	 * @return  Email_Driver|string
	 */
	public function content($content = null)
	{
		if (is_null($content))
		{
			return $this->content;
		}

		$this->content = $content;
		return $this;
	}

	/**
	 * Add content
	 *
	 * @param   string  the content to add
	 * @return  Email_Driver
	 */
	public function add_content($content)
	{
		$this->content .= $content;
		return $this;
	}

	/**
	 * Set the alt content, no args to fetch current one
	 *
	 * @param   string  the content
	 * @return  Email_Driver|string
	 */
	public function alt_content($alt_content = null)
	{
		if (is_null($alt_content))
		{
			return $this->alt_content;
		}

		$this->alt_content = $alt_content;
		return $this;
	}

	/**
	 * Add content
	 *
	 * @param   string  the content to add
	 * @return  Email_Driver
	 */
	public function add_alt_content($content)
	{
		$this->alt_content .= $content;
		return $this;
	}

	/**
	 * Sends the email.
	 *
	 * @return  bool  success
	 */
	abstract public function send();

	/**
	 * Attaches a file in the local filesystem to the email
	 *
	 * @param   string  The file to be used
	 * @return  Email_Driver
	 */
	public function attach($filename)
	{
	}

	/**
	 * Returns a debug message
	 *
	 * @return  string
	 */
	public function debug()
	{
	}
}

// end of file driver.php