<?php

$replace = array();

if (!empty($_GET['from']) && !empty($_GET['to'])) {
  $_GET['from'] = preg_replace('/^https?:\/\//', '', $_GET['from']);
  $_GET['to'] = preg_replace('/^https?:\/\//', '', $_GET['to']);
  $_GET['from'] = preg_replace('/\/.*$/', '', $_GET['from']);
  $_GET['to'] = preg_replace('/\/.*$/', '', $_GET['to']);

  if ($_GET['from'] && $_GET['to']) {
    $replace[] = array(
      'type' => 'any',
      'source' => $_GET['from'],
      'value' => $_GET['to'],
    );
    if (strpos($_SERVER['HTTP_REFERER'], 'http://') === 0) {
      $replace[] = array(
        'type' => 'any',
        'source' => 'https://' . $_GET['to'],
        'value' => 'http://' . $_GET['to'],
      );
    } else {
      $replace[] = array(
        'type' => 'any',
        'source' => 'http://' . $_GET['to'],
        'value' => 'https://' . $_GET['to'],
      );
    }
  }
}

error_reporting(E_ALL);
ini_set('display_errors', 'yes');
set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

class Faker implements Serializable
{

  public $data = '';

  public function serialize()
  {
    return $this->__serialize();
  }

  public function __serialize()
  {
    return $this->data;
  }

  public function unserialize($data)
  {
    $this->__unserialize($data);
  }

  public function __unserialize($data)
  {
    $this->data = $data;
  }
}

require __DIR__ . '/../html/wp-load.php';
echo 'WP loaded' . PHP_EOL;
print_r($replace);

/**
 * @global wpdb $wpdb
 */
require_wp_db();

if (!$wpdb->check_connection()) {
  exit('Can not connect to database' . PHP_EOL);
}

$tables = $wpdb->tables();

foreach ($tables as $table) {
  echo $table . PHP_EOL;
  flush();

  $createSyntax = $wpdb->get_var("SHOW CREATE TABLE `{$table}`", 1);
  if (preg_match('/PRIMARY KEY \(`([^`]+)`\)/', $createSyntax, $asId)) {
    $asId = $asId[1];
  } else {
    $asId = false;
  }
  $offset = 0;

  do {
    if ($asId !== false) {
      $query = "SELECT * FROM `{$table}` WHERE `{$asId}` > '{$offset}' ORDER BY `{$asId}` ASC LIMIT 100";
    } elseif ($offset) {
      $query = "SELECT * FROM `{$table}` LIMIT {$offset}, 100";
    } else {
      $query = "SELECT * FROM `{$table}` LIMIT 100";
    }
    echo $table . ($asId ? ' #' : ' @') . $offset . ' -- ' . $query . PHP_EOL;
    $rows = $wpdb->get_results($query, ARRAY_A);
    flush();

    $wasWritten = false;
    while ($row = array_shift($rows)) {
      if ($asId && isset($row[$asId])) {
        $offset = $row[$asId];
      } else {
        $offset += 1;
      }
      $wasWritten = true;

      $where = array();
      $data = array();
      if ($asId) {
        $condition = $table . '@' . $asId . '#' . $offset;
      } else {
        $condition = $table . '@limit#' . $offset;
      }
      if ($asId) {
        $where[$asId] = $row[$asId];
      }
      foreach ($row as $name => $value) {
        if (!$asId && strpos(strtolower($name), 'id') !== false) {
          $where[$name] = $value;
        }
        $replaced = db_value_normalize($value, $replace);
        if ($replaced !== $value) {
          $data[$name] = $replaced;
        }
      }
      if ($data && $where) {
        echo $condition . PHP_EOL;
        $query = $wpdb->update($table, $data, $where);
        if ($query === false) {
          echo $wpdb->error . PHP_EOL;
        }
      }
    }
    unset($result);
  } while ($wasWritten);
}

function db_value_normalize($value, $conditions, $depth = 0)
{
  if (is_array($value)) {
    foreach ($value as $k => $v) {
      $kUpdated = db_value_normalize($k, $conditions, $depth + 1);
      $vUpdated = db_value_normalize($v, $conditions, $depth + 1);
      if ($kUpdated !== $k) {
        unset($value[$k]);
      }
      if ($kUpdated !== $k || $vUpdated !== $v) {
        $value[$kUpdated] = $vUpdated;
      }
    }

    return $value;
  }
  if (is_object($value)) {
    foreach (get_object_vars($value) as $k => $v) {
      try {
        $kUpdated = db_value_normalize($k, $conditions, $depth + 1);
        $vUpdated = db_value_normalize($v, $conditions, $depth + 1);
        if ($kUpdated !== $k) {
          unset($value->$k);
        }
        if ($kUpdated !== $k || $vUpdated !== $v) {
          $value->$kUpdated = $vUpdated;
        }
      } catch (Exception $exception) {
        // nothing to do
      }
    }
    return $value;
  }
  if ($value === null) {
    return $value;
  }

  $type = 'raw';
  if (is_string($value) && is_numeric($value)) {
    // nothing to do
  } elseif (is_string($value) && $value) {

    if (preg_match_all('/\bO:\d+:"([^"]+)":\d+:/sm', $value, $classes)) {
      foreach ($classes[1] as $class) {
        if (!class_exists($class)) {
          class_alias('Faker', $class);
        }
      }
    }
    $test = @unserialize($value);
    if ($test !== false || $value == serialize(false)) {
      $type = 'serialize';
    }
    $test = null;

    $test = @json_decode($value, true);
    if (json_last_error() == JSON_ERROR_NONE) {
      $type = 'json';
    }
    $test = null;

    $test = @base64_decode($value, true);
    if ($test !== false && base64_encode($test) === $value) {
      $type = 'base64';
    }
    $test = null;
  }

  switch ($type) {
    case 'serialize':
      $value = unserialize($value);
      break;
    case 'json':
      $value = json_decode($value, true);
      break;
    case 'base64':
      $value = base64_decode($value, true);
      break;
  }

  if ($type === 'raw' && is_scalar($value)) {
    $value = db_value_replace($value, $conditions);
  } else {
    $value = db_value_normalize($value, $conditions, $depth + 1);
  }

  switch ($type) {
    case 'serialize':
      $value = serialize($value);
      break;
    case 'json':
      $value = json_encode($value);
      break;
    case 'base64':
      $value = base64_encode($value);
      break;
  }

  return $value;
}

function db_value_replace($value, $conditions)
{
  if (!is_scalar($value)) {
    echo 'Not SCALAR!' . PHP_EOL;
    print_r($value);
    exit;
  }
  foreach ($conditions as $condition) {
    switch ($condition['type']) {
      case 'exact' :
        if ($value === $condition['source']) {
          return $condition['value'];
        }
        break;
      case 'starts' :
        if (is_string($value) && strpos($value, $condition['source']) === 0) {
          $value = $condition['value'] . substr($value, strlen($condition['source']));
        }
        break;
      case 'any' :
        while (is_string($value) && strpos($value, $condition['source']) !== false) {
          $value = str_replace($condition['source'], $condition['value'], $value);
          $value = str_replace(urlencode($condition['source']), urlencode($condition['value']), $value);
        }
        break;
      case 'callback' :
        $value = call_user_func($condition['callback'], $value, $condition);
        break;
    }
  }

  return $value;
}
