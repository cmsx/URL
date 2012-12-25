<?php

namespace CMSx;

use CMSx\HTML;

class URL
{
  protected $arguments = array();
  protected $parameters = array();

  /**
   * Добавление частей к URL
   * Аргументы могут быть строками или частями URL
   * Если аргумент - массив, он обрабатывается как массив параметров
   */
  function __construct($url = null, $_ = null)
  {
    $args = func_get_args();
    foreach ($args as $u) {
      if (is_array($u)) {
        $this->addParameters($u);
      } else {
        list($a, $p) = static::Parse($u);
        $this->addArguments($a);
        $this->addParameters($p);
      }
    }
  }

  /** Добавление аргумента */
  public function addArgument($arg)
  {
    $this->arguments[count($this->arguments) + 1] = $arg;

    return $this;
  }

  /** Добавление параметра */
  public function addParameter($name, $mixed)
  {
    if (is_array($mixed)) {
      foreach ($mixed as $val) {
        $this->addParameter($name, $val);
      }
    } else {
      if (isset($this->parameters[$name])) {
        if (is_array($this->parameters[$name])) {
          $this->parameters[$name][] = $mixed;
        } else {
          $this->parameters[$name] = array($this->parameters[$name], $mixed);
        }
      } else {
        $this->parameters[$name] = $mixed;
      }
    }

    return $this;
  }

  /** Добавление параметров из ассоциативного массива */
  public function addParameters($array, $_ = null)
  {
    $args = func_get_args();
    foreach ($args as $a) {
      if (is_array($a)) {
        foreach ($a as $name => $val) {
          $this->addParameter($name, $val);
        }
      }
    }

    return $this;
  }

  /**
   * Добавление аргументов кучей
   * @param array|string $mixed - массив аргументов или строка
   */
  public function addArguments($mixed, $_ = null)
  {
    $args = func_get_args();
    foreach ($args as $mixed) {
      if (is_array($mixed)) {
        foreach ($mixed as $str) {
          $this->addArgument($str);
        }
      } else {
        $this->addArgument($mixed);
      }
    }

    return $this;
  }

  /** Аргументы текущего URL */
  public function getArguments()
  {
    return $this->arguments;
  }

  /** Параметры текущего URL */
  public function getParameters()
  {
    return $this->parameters;
  }

  /**
   * Разбор URL на части
   * Возвращает массив [аргументы, параметры]
   */
  public static function Parse($string)
  {
    $arguments = $params = array();

    //Если открыта главная страница - URL пуст
    if (empty ($string) || $string == '/') {
      return array(null, null);
    }

    //Если указаны доп.параметры - отсекаем и не учитываем при разборе
    if ($pos = strpos($string, '?')) {
      $string = substr($string, 0, $pos);
    }

    //РАЗБИРАЕМ URI НА ПАРАМЕТРЫ
    $a = explode('/', trim($string, '/'));
    $i = 1;
    if (is_array($a)) {
      foreach ($a as $str) {
        $str = urldecode($str);
        //ЕСЛИ ЕСТЬ ДВОЕТОЧИЕ - РАЗБИРАЕМ КАК ПАРАМЕТР
        if (strpos($str, ':')) {
          $arr = explode(':', $str, 2);
          if (isset ($params[$arr[0]])) {
            if (is_array($params[$arr[0]])) {
              $params[$arr[0]][] = $arr[1];
            } else {
              $params[$arr[0]] = array($params[$arr[0]], $arr[1]);
            }
          } else {
            $params[$arr[0]] = $arr[1];
          }
        } else {
          $arguments[$i++] = $str;
        }
      }
    }

    return array($arguments, $params);
  }
}