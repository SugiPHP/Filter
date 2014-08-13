<?php
/**
 * @package    SugiPHP
 * @subpackage Filter
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Filter;

use SugiPHP\Filter\Filter;
use PHPUnit_Framework_TestCase;

class FilterTest extends PHPUnit_Framework_TestCase
{
	public function testIntegers()
	{
		$filter = new Filter();

		$this->assertSame(0, $filter->int(0));
		$this->assertSame(false, $filter->int(""));
		$this->assertSame(1, $filter->int(1));
		$this->assertSame(1, $filter->int(1.0));
		$this->assertSame(false, $filter->int(1.1));
		$this->assertSame(false, $filter->int(1, 2));
		$this->assertSame(false, $filter->int(5, 2, 4));
		$this->assertSame(1, $filter->int("1"));
		$this->assertSame(false, $filter->int("1.0"));
		$this->assertSame(false, $filter->int("1a"));
		$this->assertSame(77, $filter->int("hi", false, false, 77));
	}

	public function testStrings()
	{
		$filter = new Filter();

		$this->assertSame("a", $filter->str("a"));
		$this->assertSame("1", $filter->str("1"));
		$this->assertSame("1", $filter->str(1));
		$this->assertSame("a", $filter->str(" a "));
		$this->assertSame("", $filter->str(""));
		$this->assertSame(false, $filter->str("", 1));
		$this->assertSame("a", $filter->str("a", 1));
		$this->assertSame("a", $filter->str(" a ", 1));
		$this->assertSame(false, $filter->str("ab", 1, 1));
		$this->assertSame("ab", $filter->str("ab", 1, 2));
		$this->assertSame("ab", $filter->str(" ab ", 1, 2));
		$this->assertSame(false, $filter->str(" abc ", 1, 2));
		$this->assertSame("error", $filter->str(" abc ", 1, 2, "error"));
		$this->assertSame("error", $filter->str("abc", 1, 2, "error"));
		$this->assertSame("abc", $filter->str("abc", 1, false, "error"));
	}

	/**
	 * @dataProvider URLs
	 */
	public function testURLs($url, $ok)
	{
		$filter = new Filter();

		$res = $ok ? $url : false;
		$this->assertTrue($filter->url($url) === $res);
	}

	public function URLs()
	{
		return array (
			array("igrivi.com", false),
			array("http://igrivi.com", true),
			array("http://IGriVI.COM", true),
			array("http://igrivi.com/", true),
			array("https://igrivi.com", true),
			array("ftp://igrivi.com", false),
			array("http://localhost", false),
			array("http://127.0.0.1", false),
			array("http://8.8.8.8", false),
			array("http://abc", false),
			array("http://abc.c", false),
			array("http://somedomain.com:81", true),
			array("http://somedomain.com:6", false),
			array("http://somedomain.com:123456", false),
			array("http://somedomain.com:123a", false),
			array("http://somedomain.com/:123", false),
			array("http://somedomain.com/test:123", false),
			array("http://somedomain.com:abc", false),
			array("http://somedomain.com:81/", true),
			array("http://somedomain.com:81/test", true),
			array("http://somedomain.com:81/test", true),
			array("http://порно.bg", true),
			array("http://xn--m1abbbg.bg/", true),
			array("http://президент.рф", true),
			array("http://somedomain.com/dir/people/%D0%B4", true),
			array("http://somedomain.com/dir/people/д", true),
			array("http://somedomain.com/dir/people/<", false),
			array("http://somedomain.com/con?s=+&key=test&search=1", true),
			array("http://somedomain.com/con?s=+&key=%D1%82%D0%B5%D0%A1%D1%82&search=1", true),
			array("http://somedomain.com/con?s=+&key=теСт&search=1", true),
			array("http://somedomain.com/con?s=+&key=те,Ст&search=1", true),
		);
	}

	/**
	 * @dataProvider emails
	 */
	public function testEmails($email, $ok)
	{
		$filter = new Filter();

		$res = $ok ? $email : false;
		$this->assertTrue($filter->email($email) === $res);
	}

	public function emails()
	{
		return array (
			array("Sugi@bulinfo.net", true),
			array("tza.ppa@bulinfo.net", true),
			array("Sugi@localhost", false),
			array("@localhost", false),
			array("t@.c", false),
			array("t@abc.c", false),
		);
	}

	/**
	 * @dataProvider skypes
	 */
	public function testSkype($skype, $ok)
	{
		$filter = new Filter();

		$res = $ok ? $skype : false;
		$this->assertTrue($filter->skype($skype) === $res);
	}

	public function skypes()
	{
		return array (
			array("a.L-a2b,a_la_.,-", true),
			array("fifth", false), // too short
			array("sixty1", true),
			array("1totot", false), // starts with digit
			array(".alabala", false), // starts with .
			array("_alabala", false),
			array(",alabala", false),
			array("_alabala", false),
		);
	}
}
