<?php

require_once __DIR__ . '/../init.php';

use CMSx\URL;

class GetTest extends PHPUnit_Framework_TestCase
{
  function testCleanArgs()
  {
    $a = array('one', 'two');
    $exp = array(1=>'one', 'two');
    $u = new URL('hi');
    $this->assertEquals('hi', $u->getArgument(1), 'Аргумент #1 = hi');

    $u->cleanArguments($a);

    $this->assertEquals($exp, $u->getArguments(), 'Аргументы загрузились');

    $u->cleanArguments();
    $this->assertEquals(array(), $u->getArguments(), 'Аргументы удалились');
  }

  function testCleanParams()
  {
    $a = array('hello' => 'world');
    $u = new URL(array('one' => 'two'));
    $this->assertTrue($u->hasParameter('one'), 'Параметр ONE есть.');

    $u->cleanParameters($a);

    $this->assertEquals($a, $u->getParameters(), 'Параметры загрузились');

    $u->cleanParameters();
    $this->assertEquals(array(), $u->getParameters(), 'Параметры удалились');
  }

  function testSetArg()
  {
    $u = new URL('one', 'two');
    $this->assertEquals('two', $u->getArgument(2), 'Аргумент задан');

    $u->setArgument(2, 'hi');
    $this->assertEquals('hi', $u->getArgument(2), 'Аргумент изменен');

    $u->setArgument(2);
    $this->assertFalse($u->getArgument(2), 'Аргумент был удален');
  }

  function testSetParam()
  {
    $u = new URL(array('one' => 'two'));
    $this->assertEquals('two', $u->getParameter('one'), 'Параметр задан');

    $u->setParameter('one', 2);
    $this->assertEquals(2, $u->getParameter('one'), 'Параметр изменен');

    $u->setParameter('one');
    $this->assertFalse($u->getParameter('one'), 'Параметр был удален');
  }

  function testGetArgument()
  {
    $u = new URL('one', 'two');
    $this->assertTrue($u->hasArgument(1), 'Аргумент №1 есть');
    $this->assertEquals('one', $u->getArgument(1), 'Значение аргумента №1');

    $this->assertTrue($u->hasArgument(2), 'Аргумент №2 есть');
    $this->assertEquals('two', $u->getArgument(2), 'Значение аргумента №2');

    $this->assertFalse($u->hasArgument(3), 'Аргумента №3 нет');
    $this->assertEquals('hi', $u->getArgument(3, 'hi'), 'Значение по-умолчанию');
  }

  function testHasParam()
  {
    $u = new URL(array('one' => 1));
    $this->assertTrue($u->hasParameter('one'), 'Параметр ONE есть');
    $this->assertFalse($u->hasParameter('two'), 'Параметра TWO нет');
  }

  /** @dataProvider dataParams */
  function testGetParams($value, $exp, $msg, $filter = null, $default = false)
  {
    $value = is_null($value) ? null : array('one'=>$value);
    $u = new URL($value);
    if (!is_null($value)) {
      $this->assertTrue($u->hasParameter('one'));
    }
    $this->assertTrue($exp === $u->getParameter('one', $filter, $default), $msg);
  }

  function testGetId()
  {
    $u = new URL('/hello/id:abc/');
    $this->assertEmpty($u->getId(), 'ID проверяется на is_numeric');

    $u = new URL('/hello/id:-123/');
    $this->assertEmpty($u->getId(), 'ID должен быть больше нуля');

    $u = new URL('/hello/id:123/');
    $this->assertEquals(123, $u->getId(), 'Корректный ID');
  }

  function testGetPage()
  {
    $u = new URL('/');
    $this->assertEquals(1, $u->getPage(), 'Страница не указана явно');

    $u = new URL('/page:0/');
    $this->assertEquals(1, $u->getPage(), 'Номер страницы должен быть больше 1');

    $u = new URL('/page:10/');
    $this->assertEquals(1, $u->getPage(5), 'Номер страницы должен не больше общего числа страниц');

    $u = new URL('/page:10/');
    $this->assertEquals(10, $u->getPage(), 'Номер страницы из URL');
  }

  function dataParams()
  {
    // $value, $exp, $msg, $filter = null, $default = false
    return array(
      array('one', 'one', 'Просто параметр'),
      array('one', false, 'Фильтр is_numeric', 'is_numeric'),
      array('one', 1, 'Фильтр-регулярка', '/^[0-9]+$/', 1),
      array('one', null, 'Фильтр-callable', array($this, 'isNumeric'), null),
      array(null, 1, 'Параметра нет, значение по-умолчанию', null, 1),
    );
  }

  function isNumeric($val)
  {
    return is_numeric($val);
  }
}