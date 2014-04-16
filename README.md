Filter
======

Simple filter helper functions.

Integers
--------

Validates integer value in a range within $min and $max if they are not false.
Returns default value if the given is not an integer or it's out of the range.


```php
<?php
	Filter::int($value, $min = false, $max = false, $default = false);
	// Examples:
	Filter::int(0); // 0
	Filter::int(""); // false
	Filter::int(1.0); // 1
	Filter::int(1.1); // false
	Filter::int(1, 2); // false - outside the minimum range
	Filter::int(5, 2, 4); // false - outside maximum allowed value
	Filter::int("1"); // 1
	Filter::int("1.0"); // false
	Filter::int("1a"); // false
	Filter::int("hi", false, false, 77); // 77 - Returns the default value
?>
```

Usually a developer needs to validate something that was provided by a user, so above example are rarely used.
Instead several other filters for integers are more often used:

```php
<?php
	// Validate integer from GET parameter - $_GET["key"].
	// Default value is returned if the key is not found, or cannot be converted to an integer,
	// or the value is outside the min / max range.
	Filter::intGet($key, $min_range = false, $max_range = false, $default = false);
	// for example if the URL is http://example.com?page=12
	Filter::intGet("page", 1, false, 1); // returns 12
	Filter::intGet("foo"); // returns FALSE

	// Validate integer from POST parameter - $_POST["key"].
	// Works like intGet()
	Filter::intPost($key, $min_range = false, $max_range = false, $default = false);

	// Validate integer from COOKIES - $_COOKIE[$key]
	Filter::intCookie($key, $min_range = false, $max_range = false, $default = false);

	// and from SESSION
	Filter::intSession($key, $min_range = false, $max_range = false, $default = false);
?>
```


Strings
-------

Validates string values. You can set size restrictions - minimum and maximum string lengths.
Returns default value if the given string is outside the boundaries or not a string.

```php
<?php
	Filter::str($value, $minLength = 0, $maxLength = false, $default = false);
	// Examples:
	Filter::str("a"); // "a"
	Filter::str(1); // "1"
	Filter::str(" a "); // "a"
	Filter::str(""); // ""
	Filter::str("", 1); // false
	Filter::str(" a ", 1); // "a"
	Filter::str("ab", 1, 1); // false
	Filter::str("abc", 1, 2, "error"); // "error"
	Filter::str("abc", 1, false, "error"); // "abc"

	// Slightly different version of the Filter::str() method is Filter::plain()
	// This will firstly strip all tags and then it will act exactly like Filter::str() method.
	Filter::plain($value, $minLength = 0, $maxLength = false, $default = false);
?>
```

Similar to integer filters there are some for validating string from $_GET, $_POST and $_COOKIE arrays

```php
<?php
	Filter::strGet($key, $minLength = 0, $maxLength = false, $default = false);
	Filter::strPost($key, $minLength = 0, $maxLength = false, $default = false);
	Filter::strCookie($key, $minLength = 0, $maxLength = false, $default = false);
	Filter::strSession($key, $minLength = 0, $maxLength = false, $default = false);

	// Validates plain text from $_GET, $_POST, $_COOKIE and $_SESSION parameters
	Filter::plainGet($key, $minLength = 0, $maxLength = false, $default = false);
	Filter::plainPost($key, $minLength = 0, $maxLength = false, $default = false);
	Filter::plainCookie($key, $minLength = 0, $maxLength = false, $default = false);
	Filter::plainSession($key, $minLength = 0, $maxLength = false, $default = false);
?>
```

URL's
-----

Validates URL, accepting only http or https protocols

```php
<?php
	Filter::url($value, $default = false);
	// Examples:
	Filter::url("http://igrivi.com"); // true
	Filter::url("igrivi.com"); // false
	Filter::url("http://localhost"); // false - The filter is mainly used for user inputs, so when we need URL, we intentionally don't want localhost
	Filter::url("8.8.8.8"); // false
	Filter::url("http://somedomain.com:81"); // true
	Filter::url("http://somedomain.com:6"); // false
?>
```

Emails
------

Validates email addresses. If third parameter is set to true it will check for MX record(s) for mail's domain.
If the email is not valid or the MX record is not present the default value will be returned.

```php
<?php
	Filter::email($value, $default = false, $checkMxRecord = false);
?>
```

Arrays
------

```php
<?php
	// Checks the existence of the key in a given array, returning default value if $key is not present.
	Filter::key($key, $array, $default = null);
	// Example:
	Filter::key("foo", array("one", "foo" => "bar", "foobar" => 2)); // "bar"

	// Validates $_GET[$key] value.
	Filter::get($key, $default = null);
	// Validates $_POST[$key] value.
	Filter::post($key, $default = null);
	// Validates $_COOKIE[$key] value.
	Filter::cookie($key, $default = null);
	// Validates $_SESSION[$key] value.
	Filter::session($key, $deafult = null);
?>
```
