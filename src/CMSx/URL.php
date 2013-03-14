<?php

namespace CMSx;

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
    if (!is_null($url)) {
      call_user_func_array(array($this, 'load'), func_get_args());
    }
  }

  function __toString()
  {
    return $this->toString();
  }

  /** Построение адреса */
  public function toString()
  {
    return static::Build($this->arguments, $this->parameters);
  }

  /** Формирование HTML ссылки */
  public function toHTML($text = null, $attr = null, $target = null)
  {
    return HTML::A($this->toString(), $text, $attr, $target);
  }

  /** Загрузка и парсинг URLа в объект. Если URL не указан - используется текущий */
  public function load($url = null, $_ = null)
  {
    if (is_null($url)) {
      return $this->load(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
    }

    foreach (func_get_args() as $u) {
      if (is_array($u)) {
        $this->addParameters($u);
      } else {
        list($a, $p) = static::Parse($u);
        $this->addArguments($a);
        $this->addParameters($p);
      }
    }

    return $this;
  }

  /** Удаление текущих аргументов. Если передан $array - будут загружены новые */
  public function cleanArguments(array $array = null)
  {
    $this->arguments = array();

    if (is_array($array)) {
      foreach ($array as $str) {
        $this->addArgument($str);
      }
    }

    return $this;
  }

  /** Удаление текущих параметров. Если передан $array - будут загружены новые */
  public function cleanParameters(array $array = null)
  {
    $this->parameters = array();

    if (is_array($array)) {
      foreach ($array as $name => $val) {
        $this->addParameter($name, $val);
      }
    }

    return $this;
  }

  /** Задать значение аргумента. Если $value = null аргумент будет удален. */
  public function setArgument($num, $value = null)
  {
    if (is_null($value)) {
      unset($this->arguments[$num]);
    } else {
      $this->arguments[$num] = $value;
    }

    return $this;
  }

  /** Задать значение параметра. Если $value = null параметр будет удален. */
  public function setParameter($name, $value = null)
  {
    if (is_null($value)) {
      unset($this->parameters[$name]);
    } else {
      $this->parameters[$name] = $value;
    }

    return $this;
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
   * Получение параметра из URL
   * $name - имя параметра
   * $filter - callable или регулярное выражение
   * $default - значение по-умолчанию
   */
  public function getParameter($name, $filter = null, $default = false)
  {
    if (!$this->hasParameter($name)) {
      return $default;
    }

    if ($filter) {
      if (is_callable($filter)) {
        if (!call_user_func_array($filter, array($this->parameters[$name]))) {
          return $default;
        }
      } else {
        if (!preg_match($filter, $this->parameters[$name])) {
          return $default;
        }
      }
    }

    return $this->parameters[$name];
  }

  /** Проверка, есть ли параметр */
  public function hasParameter($name)
  {
    return isset($this->parameters[$name]);
  }

  /** Проверка есть ли аргумент. Нумерация с 1 */
  public function hasArgument($num)
  {
    return isset($this->arguments[$num]);
  }

  /**
   * Получение аргумента из URL
   * $num - номер начиная с 1
   * $default - значение по-умолчанию
   */
  public function getArgument($num, $default = false)
  {
    return $this->hasArgument($num) ? $this->arguments[$num] : $default;
  }

  /** Быстрый доступ к параметру ID */
  public function getId($default = null)
  {
    return $this->getParameter(
      'id',
      function($val) {
        return is_numeric($val) && $val > 0;
      },
      $default
    );
  }

  /** Быстрый доступ к параметру Page */
  public function getPage($total_pages = null)
  {
    return $this->getParameter(
      'page',
      function($val) use ($total_pages) {
        return $val >= 1 && (!$total_pages || $val <= $total_pages);
      },
      1
    );
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
      return array($arguments, $params);
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

  /**
   * Построение URL из аргументов и параметров
   */
  public static function Build(array $args = null, array $params = null)
  {
    $url     = '/';
    $endings = array();
    if (is_array($args)) {
      foreach ($args as $str) {
        if (0 === strpos($str, '#')) {
          $endings[1] = $str;
        } elseif (false !== strpos($str, '.')) {
          $endings[0] = $str;
        } else {
          $url .= $str . '/';
        }
      }
    }

    if (is_array($params)) {
      foreach ($params as $name => $val) {
        if (is_array($val)) {
          foreach ($val as $v) {
            $url .= $name . ':' . $v . '/';
          }
        } else {
          $url .= $name . ':' . $val . '/';
        }
      }
    }

    ksort($endings);

    return $url . join('', $endings);
  }

  /** Транслитерация */
  public static function Translit($str)
  {
    $converter = array(
      'а' => 'a', 'б' => 'b', 'в' => 'v',
      'г' => 'g', 'д' => 'd', 'е' => 'e',
      'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
      'и' => 'i', 'й' => 'y', 'к' => 'k',
      'л' => 'l', 'м' => 'm', 'н' => 'n',
      'о' => 'o', 'п' => 'p', 'р' => 'r',
      'с' => 's', 'т' => 't', 'у' => 'u',
      'ф' => 'f', 'х' => 'h', 'ц' => 'c',
      'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
      'ь' => "'", 'ы' => 'y', 'ъ' => "'",
      'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

      'А' => 'A', 'Б' => 'B', 'В' => 'V',
      'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
      'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
      'И' => 'I', 'Й' => 'Y', 'К' => 'K',
      'Л' => 'L', 'М' => 'M', 'Н' => 'N',
      'О' => 'O', 'П' => 'P', 'Р' => 'R',
      'С' => 'S', 'Т' => 'T', 'У' => 'U',
      'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
      'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
      'Ь' => "'", 'Ы' => 'Y', 'Ъ' => "'",
      'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    );

    $s = preg_replace('/[^a-z0-9_-]/is', '_', strtr($str, $converter));

    return trim(preg_replace('/[_]{2,}/', '_', $s), '_');
  }
}