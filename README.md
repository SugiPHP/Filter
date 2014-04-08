Filter
======

Simple filter helper functions.

```php
<?php
	// Validates integer value in a range within $min and $max if they are not false.
	// Returns default value if the given is not an integer or it's out of the range.
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
