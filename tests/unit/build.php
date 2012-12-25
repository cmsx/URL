<?php

require_once __DIR__ . '/../init.php';

use CMSx\URL;

class BuildTest extends PHPUnit_Framework_TestCase
{
  /** @dataProvider dataBuild */
  function testBuild($args, $params, $exp, $msg = null)
  {
    $this->assertEquals($exp, Url::Build($args, $params), $msg);
  }

  function testToString()
  {
    $u = new URL('one', 'two', array('hello' => 'john'));
    $this->assertEquals('/one/two/hello:john/', $u->toString(), 'Построение адреса');
    $this->assertEquals($u->toString(), (string)$u, 'Приведение объекта к строке');
    $this->assertEquals(
      '<a class="hello" href="/one/two/hello:john/" target="_blank">hi</a>',
      $u->toHTML('hi', 'hello', '_blank'),
      'HTML ссылка'
    );
  }

  function dataBuild()
  {
    return array(
      array(null, null, '/', 'Пустой URL - главная'),

      array(
        array('test', 'me'),
        null,
        '/test/me/',
        'Без параметров'
      ),

      array(
        array('test', 'me'),
        array('id' => 12, 'hello' => 'world'),
        '/test/me/id:12/hello:world/',
        'Простые параметры'
      ),

      array(
        array('test', 'me'),
        array('id' => array(12, 15, 16), 'hello' => 'world'),
        '/test/me/id:12/id:15/id:16/hello:world/',
        'Сложные параметры'
      ),

      array(
        array('test', 'file.txt'),
        array('one' => 'two'),
        '/test/one:two/file.txt',
        'Аргумент с точкой = файл'
      ),

      array(
        array('test', '#anchor'),
        array('one' => 'two'),
        '/test/one:two/#anchor',
        'Аргумент с # вначале = анкор'
      ),

      array(
        array('test', '#anchor', 'file.html'),
        array('one' => 'two'),
        '/test/one:two/file.html#anchor',
        'Аргументы с анкором и файлом'
      ),
    );
  }
}