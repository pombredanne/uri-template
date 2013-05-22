<?php

require_once 'PHPUnit/Autoload.php';
require_once 'src.php';


/**
 * Test class for uri_template().
 */
class UriTemplateTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Runs the test methods of this class.
	 */
	public static function main()
	{
		$suite  = new PHPUnit_Framework_TestSuite(__CLASS__);
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	public function testLevel1()
	{
		$variables = array(
			'var'   => "value",
			'hello' => "Hello World!",
		);
		$templates = array(
			'{var}'   => "value",
			'{hello}' => "Hello%20World%21",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testLevel2()
	{
		$variables = array(
			'var'   => "value",
			'hello' => "Hello World!",
			'path'  => "/foo/bar",
		);
		$templates = array(
			'{+var}'           => "value",
			'{+hello}'         => "Hello%20World!",
			'{+path}/here'     => "/foo/bar/here",
			'here?ref={+path}' => "here?ref=/foo/bar",

			'X{#var}'          => "X#value",
			'X{#hello}'        => "X#Hello%20World!",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testLevel3()
	{
		$variables = array(
			'var'   => "value",
			'hello' => "Hello World!",
			'empty' => "",
			'path'  => "/foo/bar",
			'x'     => "1024",
			'y'     => "768",
		);
		$templates = array(
			'map?{x,y}'      => "map?1024,768",
			'{x,hello,y}'    => "1024,Hello%20World%21,768",

			'{+x,hello,y}'   => "1024,Hello%20World!,768",
			'{+path,x}/here' => "/foo/bar,1024/here",

			'{#x,hello,y}'   => "#1024,Hello%20World!,768",
			'{#path,x}/here' => "#/foo/bar,1024/here",

			'X{.var}'        => "X.value",
			'X{.x,y}'        => "X.1024.768",

			'{/var}'         => "/value",
			'{/var,x}/here'  => "/value/1024/here",

			'{;x,y}'         => ";x=1024;y=768",
			'{;x,y,empty}'   => ";x=1024;y=768;empty",

			'{?x,y}'         => "?x=1024&y=768",
			'{?x,y,empty}'   => "?x=1024&y=768&empty=",

			'?fixed=yes{&x}' => "?fixed=yes&x=1024",
			'{&x,y,empty}'   => "&x=1024&y=768&empty=",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testLevel4()
	{
		$variables = array(
			'var'   => "value",
			'hello' => "Hello World!",
			'path'  => "/foo/bar",
			'list'  => array("red", "green", "blue"),
			'keys'  => array('semi' => ";", 'dot' => ".", 'comma' => ","),
		);
		$templates = array(
			'{var:3}'         => "val",
			'{var:30}'        => "value",
			'{list}'          => "red,green,blue",
			'{list*}'         => "red,green,blue",
			'{keys}'          => "semi,%3B,dot,.,comma,%2C",
			'{keys*}'         => "semi=%3B,dot=.,comma=%2C",

			'{+path:6}/here'  => "/foo/b/here",
			'{+list}'         => "red,green,blue",
			'{+list*}'        => "red,green,blue",
			'{+keys}'         => "semi,;,dot,.,comma,,",
			'{+keys*}'        => "semi=;,dot=.,comma=,",

			'{#path:6}/here'  => "#/foo/b/here",
			'{#list}'         => "#red,green,blue",
			'{#list*}'        => "#red,green,blue",
			'{#keys}'         => "#semi,;,dot,.,comma,,",
			'{#keys*}'        => "#semi=;,dot=.,comma=,",

			'X{.var:3}'       => "X.val",
			'X{.list}'        => "X.red,green,blue",
			'X{.list*}'       => "X.red.green.blue",
			'X{.keys}'        => "X.semi,%3B,dot,.,comma,%2C",
			'X{.keys*}'       => "X.semi=%3B.dot=..comma=%2C",

			'{/var:1,var}'    => "/v/value",
			'{/list}'         => "/red,green,blue",
			'{/list*}'        => "/red/green/blue",
			'{/list*,path:4}' => "/red/green/blue/%2Ffoo",
			'{/keys}'         => "/semi,%3B,dot,.,comma,%2C",
			'{/keys*}'        => "/semi=%3B/dot=./comma=%2C",

			'{;hello:5}'      => ";hello=Hello",
			'{;list}'         => ";list=red,green,blue",
			'{;list*}'        => ";list=red;list=green;list=blue",
			'{;keys}'         => ";keys=semi,%3B,dot,.,comma,%2C",
			'{;keys*}'        => ";semi=%3B;dot=.;comma=%2C",

			'{?var:3}'        => "?var=val",
			'{?list}'         => "?list=red,green,blue",
			'{?list*}'        => "?list=red&list=green&list=blue",
			'{?keys}'         => "?keys=semi,%3B,dot,.,comma,%2C",
			'{?keys*}'        => "?semi=%3B&dot=.&comma=%2C",

			'{&var:3}'        => "&var=val",
			'{&list}'         => "&list=red,green,blue",
			'{&list*}'        => "&list=red&list=green&list=blue",
			'{&keys}'         => "&keys=semi,%3B,dot,.,comma,%2C",
			'{&keys*}'        => "&semi=%3B&dot=.&comma=%2C",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testPrefixValues()
	{
		$variables = array(
			'var'  => "value",
			'semi' => ";",
		);
		$templates = array(
			'{var}'    => "value",
			'{var:20}' => "value",
			'{var:3}'  => "val",
			'{semi}'   => "%3B",
			'{semi:2}' => "%3B",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testCompositeValues()
	{
		$variables = array(
			'year' => array("1965", "2000", "2012"),
			'dom'  => array("example", "com"),
		);
		$templates = array(
			'find{?year*}' => "find?year=1965&year=2000&year=2012",
			'www{.dom*}'   => "www.example.com",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	// Expression Expansion Tests
	protected $_expression_expension_variables = array(
		'count'      => array("one", "two", "three"),
		'dom'        => array("example", "com"),
		'dub'        => "me/too",
		'hello'      => "Hello World!",
		'half'       => "50%",
		'var'        => "value",
		'who'        => "fred",
		'base'       => "http://example.com/home/",
		'path'       => "/foo/bar",
		'list'       => array('red', 'green', 'blue'),
		'keys'       => array('semi' => ";", 'dot' => ".", 'comma' => ","),
		'v'          => "6",
		'x'          => "1024",
		'y'          => "768",
		'empty'      => "",
		'empty_keys' => array(),
		'undef'      => null,
	);

	public function testVariableExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{count}'   => "one,two,three",
			'{count*}'  => "one,two,three",
			'{/count}'  => "/one,two,three",
			'{/count*}' => "/one/two/three",
			'{;count}'  => ";count=one,two,three",
			'{;count*}' => ";count=one;count=two;count=three",
			'{?count}'  => "?count=one,two,three",
			'{?count*}' => "?count=one&count=two&count=three",
			'{&count*}' => "&count=one&count=two&count=three",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testSimpleStringExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{var}'       => "value",
			'{hello}'     => "Hello%20World%21",
			'{half}'      => "50%25",
			'O{empty}X'   => "OX",
			'O{undef}X'   => "OX",
			'{x,y}'       => "1024,768",
			'{x,hello,y}' => "1024,Hello%20World%21,768",
			'?{x,empty}'  => "?1024,",
			'?{x,undef}'  => "?1024",
			'?{undef,y}'  => "?768",
			'{var:3}'     => "val",
			'{var:30}'    => "value",
			'{list}'      => "red,green,blue",
			'{list*}'     => "red,green,blue",
			'{keys}'      => "semi,%3B,dot,.,comma,%2C",
			'{keys*}'     => "semi=%3B,dot=.,comma=%2C",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testReservedExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{+var}'              => "value",
			'{+hello}'            => "Hello%20World!",
			'{+half}'             => "50%25",

			'{base}index'         => "http%3A%2F%2Fexample.com%2Fhome%2Findex",
			'{+base}index'        => "http://example.com/home/index",
			'O{+empty}X'          => "OX",
			'O{+undef}X'          => "OX",

			'{+path}/here'        => "/foo/bar/here",
			'here?ref={+path}'    => "here?ref=/foo/bar",
			'up{+path}{var}/here' => "up/foo/barvalue/here",
			'{+x,hello,y}'        => "1024,Hello%20World!,768",
			'{+path,x}/here'      => "/foo/bar,1024/here",

			'{+path:6}/here'      => "/foo/b/here",
			'{+list}'             => "red,green,blue",
			'{+list*}'            => "red,green,blue",
			'{+keys}'             => "semi,;,dot,.,comma,,",
			'{+keys*}'            => "semi=;,dot=.,comma=,",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testFragmentExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{#var}'         => "#value",
			'{#hello}'       => "#Hello%20World!",
			'{#half}'        => "#50%25",
			'foo{#empty}'    => "foo#",
			'foo{#undef}'    => "foo",
			'{#x,hello,y}'   => "#1024,Hello%20World!,768",
			'{#path,x}/here' => "#/foo/bar,1024/here",
			'{#path:6}/here' => "#/foo/b/here",
			'{#list}'        => "#red,green,blue",
			'{#list*}'       => "#red,green,blue",
			'{#keys}'        => "#semi,;,dot,.,comma,,",
			'{#keys*}'       => "#semi=;,dot=.,comma=,",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testLabelExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{.who}'          => ".fred",
			'{.who,who}'      => ".fred.fred",
			'{.half,who}'     => ".50%25.fred",
			'www{.dom*}'      => "www.example.com",
			'X{.var}'         => "X.value",
			'X{.empty}'       => "X.",
			'X{.undef}'       => "X",
			'X{.var:3}'       => "X.val",
			'X{.list}'        => "X.red,green,blue",
			'X{.list*}'       => "X.red.green.blue",
			'X{.keys}'        => "X.semi,%3B,dot,.,comma,%2C",
			'X{.keys*}'       => "X.semi=%3B.dot=..comma=%2C",
			'X{.empty_keys}'  => "X",
			'X{.empty_keys*}' => "X",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testPathSegmentExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{/who}'          => "/fred",
			'{/who,who}'      => "/fred/fred",
			'{/half,who}'     => "/50%25/fred",
			'{/who,dub}'      => "/fred/me%2Ftoo",
			'{/var}'          => "/value",
			'{/var,empty}'    => "/value/",
			'{/var,undef}'    => "/value",
			'{/var,x}/here'   => "/value/1024/here",
			'{/var:1,var}'    => "/v/value",
			'{/list}'         => "/red,green,blue",
			'{/list*}'        => "/red/green/blue",
			'{/list*,path:4}' => "/red/green/blue/%2Ffoo",
			'{/keys}'         => "/semi,%3B,dot,.,comma,%2C",
			'{/keys*}'        => "/semi=%3B/dot=./comma=%2C",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testPathStyleParameterExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{;who}'         => ";who=fred",
			'{;half}'        => ";half=50%25",
			'{;empty}'       => ";empty",
			'{;v,empty,who}' => ";v=6;empty;who=fred",
			'{;v,bar,who}'   => ";v=6;who=fred",
			'{;x,y}'         => ";x=1024;y=768",
			'{;x,y,empty}'   => ";x=1024;y=768;empty",
			'{;x,y,undef}'   => ";x=1024;y=768",
			'{;hello:5}'     => ";hello=Hello",
			'{;list}'        => ";list=red,green,blue",
			'{;list*}'       => ";list=red;list=green;list=blue",
			'{;keys}'        => ";keys=semi,%3B,dot,.,comma,%2C",
			'{;keys*}'       => ";semi=%3B;dot=.;comma=%2C",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testFormStyleQueryExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{?who}'       => "?who=fred",
			'{?half}'      => "?half=50%25",
			'{?x,y}'       => "?x=1024&y=768",
			'{?x,y,empty}' => "?x=1024&y=768&empty=",
			'{?x,y,undef}' => "?x=1024&y=768",
			'{?var:3}'     => "?var=val",
			'{?list}'      => "?list=red,green,blue",
			'{?list*}'     => "?list=red&list=green&list=blue",
			'{?keys}'      => "?keys=semi,%3B,dot,.,comma,%2C",
			'{?keys*}'     => "?semi=%3B&dot=.&comma=%2C",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}

	public function testFormStyleContinuationExpansion()
	{
		$variables = $this->_expression_expension_variables;
		$templates = array(
			'{&who}'         => "&who=fred",
			'{&half}'        => "&half=50%25",
			'?fixed=yes{&x}' => "?fixed=yes&x=1024",
			'{&x,y,empty}'   => "&x=1024&y=768&empty=",
			'{&x,y,undef}'   => "&x=1024&y=768",

			'{&var:3}'       => "&var=val",
			'{&list}'        => "&list=red,green,blue",
			'{&list*}'       => "&list=red&list=green&list=blue",
			'{&keys}'        => "&keys=semi,%3B,dot,.,comma,%2C",
			'{&keys*}'       => "&semi=%3B&dot=.&comma=%2C",
		);

		foreach($templates as $template => $expected)
		{
			$value = uri_template($template, $variables);
			$this->assertEquals($expected, $value);
		}
	}
}

// Call MyFileTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
	UriTemplateTest::main();
}

?>