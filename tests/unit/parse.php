<?php

require_once __DIR__ . '/../init.php';

use CMSx\URL;

class ParseTest extends PHPUnit_Framework_TestCase
{
  function testParse()
  {
    $exp = array(array(1 => 'test', 'me'), array());
    $act = URL::Parse('/test/me/');
    $this->assertEquals($exp, $act, 'Два аргумента');

    $exp = array(array(1 => 'test', 'me'), array('id' => 12, 'some' => 'thing'));
    $act = URL::Parse('/test/me/id:12/some:thing/');
    $this->assertEquals($exp, $act, 'Два аргумента и параметры');

    $exp = array(array(1 => 'test', 'me'), array('id' => array(12, 13)));
    $act = URL::Parse('/test/me/id:12/id:13/');
    $this->assertEquals($exp, $act, 'Два аргумента и параметр-массив');

    $exp = array(array(1 => 'русский', 'язык'), array());
    $act = URL::Parse('/русский/язык/');
    $this->assertEquals($exp, $act, 'Русский язык в URL');

    $exp = array(array(1 => 'test', 'me', '#some'), array());
    $act = URL::Parse('/test/me/#some');
    $this->assertEquals($exp, $act, 'URL с #анкором');

    $exp = array(array(1 => 'test', 'me', 'file.txt'), array());
    $act = URL::Parse('/test/me/file.txt');
    $this->assertEquals($exp, $act, 'URL с файлом и расширением');
  }

  function testAddArgument()
  {
    $u = new URL;
    $u->addArgument('one')
      ->addArgument('two');
    $this->assertEquals(array(1=>'one', 2=>'two'), $u->getArguments(), 'Аргументы');
  }

  function testAddArguments()
  {
    $exp = array(1=>'one', 'two', 'three');

    $u = new URL;
    $u->addArguments('one', 'two', 'three');
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы переданные как строки');

    $u = new URL;
    $u->addArguments(array('one', 'two', 'three'));
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы переданные как массив');

    $u = new URL;
    $u->addArguments(array('one', 'two'), 'three');
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы переданные как строки и массив #1');

    $u = new URL;
    $u->addArguments('one', array('two', 'three'));
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы переданные как строки и массив #1');
  }

  function testAddParameter()
  {
    $u = new URL;
    $u->addParameter('hello', 'world')
      ->addParameter('name', 'john');
    $this->assertEquals(array('hello' => 'world', 'name' => 'john'), $u->getParameters(), 'Простые параметры');

    $u = new URL;
    $u->addParameter('one', 1)
      ->addParameter('one', 2);
    $this->assertEquals(array('one' => array(1, 2)), $u->getParameters(), 'Параметры добавленные в массив по очереди');

    $u->addParameter('one', array(3, 4));
    $this->assertEquals(array('one' => array(1, 2, 3, 4)), $u->getParameters(), 'Параметры добавленные массивом');
  }

  function testAddParameters()
  {
    $exp = array('hello' => 'world', 'name' => array('john', 'doe'));
    $u = new URL;
    $u->addParameters(array('hello' => 'world'), array('name' => array('john', 'doe')));
    $this->assertEquals($exp, $u->getParameters(), 'Параметры переданные кучей');
  }

  function testConstructor()
  {
    $exp = array(1=>'one', 'two', 'three');

    $u   = new URL('one', 'two', 'three');
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы #1');
    $this->assertTrue(is_array($u->getParameters()), 'Параметры - массив');
    $this->assertEquals(0, count($u->getParameters()), 'Массив параметров пустой');

    $p1 = array('hello' => 'world');
    $p2 = array('world' => 'hello');
    $u = new URL('/one/two/', 'three', $p1, $p2);
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы #2');
    $this->assertEquals(array_merge($p1, $p2), $u->getParameters(), 'Массив параметров');

    $u = new URL('/one/two/', 'three', '/hello:world/', $p2);
    $this->assertEquals($exp, $u->getArguments(), 'Аргументы #2');
    $this->assertEquals(array_merge($p1, $p2), $u->getParameters(), 'Массив параметров');
  }
}