<?php

require_once __DIR__ . '/../init.php';

use CMSx\URL;

class TranslitTest extends PHPUnit_Framework_TestCase
{
  /** @dataProvider dataTranslit */
  function testTranslit($str, $exp, $msg)
  {
    $this->assertEquals($exp, URL::Translit($str), $msg);
  }

  function dataTranslit()
  {
    return array(
      array('привет', 'privet', 'Строчные'),
      array('ПРИВЕТ', 'PRIVET', 'Заглавные'),
      array('ч щ ю ё ЁЧЩЮ', 'ch_sch_yu_e_EChSchYu', 'Глючные символы'),
      array('Предложение, со знаками!', 'Predlozhenie_so_znakami', 'Смешанный текст'),
      array(' Ненужные   пробелы! ', 'Nenuzhnye_probely', 'Обрезка лишних подчеркиваний'),
    );
  }
}