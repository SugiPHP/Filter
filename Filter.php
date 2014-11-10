<?php
/**
 * @package    SugiPHP
 * @subpackage Filter
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Filter;

/**
 * Filter - a helper class which wraps a filter_var() function.
 */
class Filter
{
	/**
	 * Encoding used in multi-byte string functions.
	 *
	 * @var string
	 */
	public $encoding = "UTF-8";

	/**
	 * Validates integer value.
	 *
	 * @param  mixed   $value Integer or string
	 * @param  integer $min
	 * @param  integer $max
	 * @param  mixed   $default What to be returned if the filter fails
	 * @return mixed
	 */
	public function int($value, $min = false, $max = false, $default = false)
	{
		$options = array("options" => array());
		if (isset($default)) {
			$options["options"]["default"] = $default;
		}
		if (is_numeric($min)) {
			$options["options"]["min_range"] = $min;
		}
		if (is_numeric($max)) {
			$options["options"]["max_range"] = $max;
		}

		// We really DO NOT need to validate user inputs like 010 or 0x10
		// so this is not needed: $options["flags"] = FILTER_FLAG_ALLOW_OCTAL | FILTER_FLAG_ALLOW_HEX;
		// If in the code we use something like $this->int(010) this is the
		// same as $this->int(8) - so it will pass and return 8
		// But if we read it from user input, a file etc, it should fail by
		// default. Example Right padding some currencies like 0010.00 USD
		return filter_var($value, FILTER_VALIDATE_INT, $options);
	}

	/**
	 * Validates string value.
	 *
	 * @param  string  $value
	 * @param  integer $minLength
	 * @param  mixed   $maxLength
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function str($value, $minLength = 0, $maxLength = false, $default = false)
	{
		$value = trim($value);
		if ((!empty($minLength) && (mb_strlen($value, $this->encoding) < $minLength)) ||
			(!empty($maxLength) && (mb_strlen($value, $this->encoding) > $maxLength))) {
				return $default;
		}

		return (string) $value;
	}

	/**
	 * Validates string and is removing tags from it.
	 *
	 * @param  string $value
	 * @param  integer $minLength
	 * @param  mixed $maxLength
	 * @param  mixed $default
	 * @return mixed
	 */
	public function plain($value, $minLength = 0, $maxLength = false, $default = false)
	{
		$value = strip_tags($value);

		return $this->str($value, $minLength, $maxLength, $default);
	}

	/**
	 * Validates URL.
	 * Does not validate FTP URLs like ftp://example.com.
	 * It only accepts http or https
	 * http://localhost is also not valid since we want some user's url,
	 * not localhost
	 * http://8.8.8.8 is not accepted, it's IP, not URL
	 *
	 * @param  string $value - URL to filter
	 * @param  mixed $default Return value if filter fails
	 * @return mixed
	 */
	public function url($value, $default = false)
	{
		// starting with http:// or https:// no more protocols are accepted
		$protocol = "http(s)?://";
		$userpass = "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
		$domain = "([\w_-]+\.)+[\w_-]{2,}"; // at least x.xx
		$port = "(\:[0-9]{2,5})?";// starting with colon and followed by 2 up to 5 digits
		$path = "(\/([\w%+\$_-]\.?)+)*\/?"; // almost anything
		$query = "(\?[a-z+&\$_.-][\w;:@/&%=+,\$_.-]*)?";
		$anchor = "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

		return (preg_match("~^".$protocol.$userpass.$domain.$port.$path.$query.$anchor."$~iu", $value)) ? $value : $default;
	}

	/**
	 * Validates email.
	 *
	 * @param  string $value
	 * @param  mixed $default - default value to return on validation failure
	 * @param  boolean $checkMxRecord - check existence of MX record. If check fails default value will be returned.
	 * @return mixed
	 */
	public function email($value, $default = false, $checkMxRecord = false)
	{
		if (!$value = filter_var($value, FILTER_VALIDATE_EMAIL)) {
			return $default;
		}
		$dom = explode("@", $value);
		$dom = array_pop($dom);
		if (!$this->url("http://$dom")) {
			return $default;
		}

		return (!$checkMxRecord || checkdnsrr($dom, "MX")) ? $value : $default;
	}

	/**
	 * Validates an IP address (IPv4 only)
	 *
	 * @param  string  $value IP address
	 * @param  mixed   $default Default value to return if validation fails.
	 * @param  boolean $acceptPrivate  Accept private addresses like 192.168.0.1
	 * @param  boolean $acceptReserved Accept reserved addresses like 0.0.0.0
	 * @return mixed   the given IP address or $default value if validation fails.
	 */
	public function ipv4($value, $default = false, $acceptPrivate = false, $acceptReserved = false)
	{
		$flags = FILTER_FLAG_IPV4;
		if (!$acceptPrivate) {
			$flags |= FILTER_FLAG_NO_PRIV_RANGE;
		}
		if (!$acceptReserved) {
			$flags |= FILTER_FLAG_NO_RES_RANGE;
		}

		return ($value = filter_var($value, FILTER_VALIDATE_IP, $flags)) ? $value : $default;
	}

	/**
	 * Validates Skype names.
	 * Skype Name must be between 6 and 32 characters.
	 * It must start with a letter and can contain only letters, numbers,
	 * full stop (.), comma (,), dash (-), underscore (_)
	 *
	 * @param  string $value Skype name to validate
	 * @param  mixed $default Return value if filter fails
	 * @return mixed String on success (value) or $default on failure
	 */
	public function skype($value, $default = false)
	{
		return (preg_match("~^[a-z]([a-z0-9-_,\.]){5,31}$~i", $value)) ? $value : $default;
	}

	/**
	 * Validates key existence in the given array.
	 *
	 * @param  string $key
	 * @param  array $array
	 * @param  mixed $default
	 * @return mixed
	 */
	public function key($key, $array, $default = null)
	{
		return (isset($array) && is_array($array) && array_key_exists($key, $array)) ? $array[$key] : $default;
	}

	/**
	 * Validates $_GET[$key] value.
	 *
	 * @param  string $key Key parameter of $_GET
	 * @param  mixed $default Return value if filter fails
	 * @return mixed - string on success ($_GET[$key] value) or $default on failure
	 */
	public function get($key, $default = null)
	{
		return $this->key($key, $_GET, $default);
	}

	/**
	 * Validates $_POST[$key] value.
	 *
	 * @param  string $key Key parameter of $_POST
	 * @param  mixed $default Return value if filter fails
	 * @return mixed - string on success ($_POST[$key] value) or $default on failure
	 */
	public function post($key, $default = null)
	{
		return $this->key($key, $_POST, $default);
	}

	/**
	 * Validates $_COOKIE[$key] value.
	 *
	 * @param  string $key Key parameter of $_COOKIE
	 * @param  mixed $default Return value if filter fails
	 * @return mixed String on success ($_COOKIE[$key] value) or $default on failure
	 */
	public function cookie($key, $default = null)
	{
		return $this->key($key, $_COOKIE, $default);
	}


	/**
	 * Validates $_SESSION[$key] value.
	 *
	 * @param  string $key Key parameter of $_SESSION
	 * @param  mixed $default Return value if key is not found
	 * @return mixed - string on success ($_SESSION[$key] value) or $default on failure
	 */
	public function session($key, $default = null)
	{
		return $this->key($key, $_SESSION, $default);
	}

	/**
	 * Validate string from GET parameter - $_GET["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned if validation fails
	 * @return mixed
	 */
	public function strGet($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->str($this->get($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validate string from POST paramether - $_POST["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function strPost($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->str($this->post($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validate string from COOKIE - $_COOKIE["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function strCookie($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->str($this->cookie($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validate string from $_SESSION["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function strSession($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->str($this->session($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validates plain text from GET paramether - $_GET["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function plainGet($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->plain($this->get($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validates plain text from POST paramether - $_POST["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function plainPost($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->plain($this->post($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validates plain text from COOKIE - $_COOKIE["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function plainCookie($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->plain($this->cookie($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validates plain text from $_SESSION["key"].
	 *
	 * @param  string $key
	 * @param  integer $minLength
	 * @param  mixed $maxLength Integer or false when there is no limit
	 * @param  mixed $default - default value will be returned when validation fails
	 * @return mixed
	 */
	public function plainSession($key, $minLength = 0, $maxLength = false, $default = false)
	{
		return $this->plain($this->session($key), $minLength, $maxLength, $default);
	}

	/**
	 * Validate integer from GET parameter - $_GET["key"].
	 *
	 * @param  string $key
	 * @param  mixed $minRange Integer or false not to check
	 * @param  mixed $maxRange Integer or false when there is no limit
	 * @param  mixed $default Integer will be returned when validation succeeds,
	 *         or default value of failure
	 * @return mixed
	 */
	public function intGet($key, $minRange = false, $maxRange = false, $default = false)
	{
		return $this->int($this->get($key), $minRange, $maxRange, $default);
	}

	/**
	 * Validate integer from POST parameter - $_POST["key"].
	 *
	 * @param  string $key
	 * @param  mixed $minRange Integer or false not to check
	 * @param  mixed $maxRange Integer or false when there is no limit
	 * @param  mixed $default Integer will be returned when validation succeeds,
	 *         or default value of failure
	 * @return mixed
	 */
	public function intPost($key, $minRange = false, $maxRange = false, $default = false)
	{
		return $this->int($this->post($key), $minRange, $maxRange, $default);
	}

	/**
	 * Validate integer from COOKIE - $_COOKIE["key"].
	 *
	 * @param  string $key
	 * @param  mixed $minRange Integer or false not to check
	 * @param  mixed $maxRange Integer or false when there is no limit
	 * @param  mixed $default Integer will be returned when validation succeeds,
	 *         or default value of failure
	 * @return mixed
	 */
	public function intCookie($key, $minRange = false, $maxRange = false, $default = false)
	{
		return $this->int($this->cookie($key), $minRange, $maxRange, $default);
	}

	/**
	 * Validate integer from $_SESSION["key"].
	 *
	 * @param  string $key
	 * @param  mixed $minRange Integer or false not to check
	 * @param  mixed $maxRange Integer or false when there is no limit
	 * @param  mixed $default Integer will be returned when validation succeeds,
	 *         or default value of failure
	 * @return mixed
	 */
	public function intSession($key, $minRange = false, $maxRange = false, $default = false)
	{
		return $this->int($this->session($key), $minRange, $maxRange, $default);
	}
}
