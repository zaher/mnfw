<?php
/**
 * MNFW/DB 
 *
 * @license   The MIT License (MIT) (http://opensource.org/licenses/MIT)
 * @author    Zaher Dirkey <zaher at parmaja dot com>
 * @author    Jihad Khalifa <jihad at parmaja dot com>
 * 
 **/

/**
 * Create new connection to database using db_type
 * 
 */

function new_connection($db_type, $name, $username = '', $password = '', $host = '', $prefix = '', $permanent = false)
{
  $db_class = $db_type . '_connection_class';
  $db_classfile = __DIR__.'/'.$db_type.'.php';
  require($db_classfile);
  $connection = new $db_class($db_type, $host, $username, $password, $name, $prefix, $permanent);
  return $connection;
}

class SQL_Where {
  public $name;
  public $value = null;
  public $compare = '=';
  
  function __construct($name, $value = null, $compare = '=') {
    $this->name = $name ;
    $this->value = $value;
    $this->compare = $compare;
  }

  function __destruct() {
  }  
}

/**
  connection_class
*/
class connection_class
{
  var $type = '';
  var $dbname = '';
  var $host = '';
  var $prefix = '';
  var $username = '';
  var $password = '';
  var $history= array();
  var $lower_fields = false;//make all fields to lower case for fetch_assoc 

  var $handle;
  
  function connection_class($db_type, $host, $username, $password, $name, $prefix)
  {
    $this->type = $db_type;
    $this->host = $host;
    $this->username = $username;
    $this->password = $password;
    $this->name = $name;
    $this->prefix = $prefix;
  }
  
  function error($error)
  {
  }

  function connect()
  {
    return $this->do_connect();
  }

  function disconnect()
  {
    $this->do_disconnect();
    unset($this->handle);
  }

  function new_session()
  {
    $session  = $this->do_new_session();
    $session->connection = $this;
    return $session;
  }

  function open() {
    $this->connect();
  }

  function close() {
    $this->disconnect();
  }
}

/**
  session_class
  
  Not all database support sessions or multiple transactions in one connection
*/

define('as_simple', 0);
define('as_array', 1);
define('as_objects', 2);

class session_class
{
  var $handle;

  function new_command()
  {
    $command = $this->do_new_command();
    $command->session =$this;
    $command->connection =$this->connection;
    return $command;
  }

  function start()
  {
    if (!isset($this->connection->handle))
      $this->connection->connect();
    return $this->do_start();
  }
  
  function commit($retain = false)
  {
    $this->do_commit($retain);
    unset($this->handle);
  }
  
  function rollback($retain = false)
  {
    $this->do_rollback($retain);
    unset($this->handle);
  }

  function stop()
  {
    $this->commit(false);
  }

  function query($sql)
  {
    $cmd = $this->new_command();
    $cmd->prepare($sql);
    $cmd->execute(false);
    return $cmd;
  }

  function query_all($sql, $fetch_as = as_simple)
  {
    $cmd = $this->new_command();
    $cmd->prepare($sql);
    $cmd->execute(false);
    return $cmd->fetch_all($fetch_as);
  }

  public function cmd($sql) {
    $cmd = $this->new_command();
    $cmd->sql = $sql;
    return $cmd;
  }
  
  public function execute($sql)
  {
    $cmd = $this->new_command();
    $cmd->prepare($sql);
    $cmd->execute(true);
    return $cmd;
  }

  /**
    for install script
  */

  public function run($sql)
  {
    $cmd = $this->new_command();
    $cmd->run($sql);
    return $cmd;
  }

  function install($script)
  {
    $this->do_before_install();

    $file = file_get_contents($script);
    $scripts = preg_split("#\^#", $file, -1, PREG_SPLIT_NO_EMPTY);
    $a[]='#__prefix__#';
    $b[]=$this->connection->prefix;
    foreach ($scripts as $key => $sql)
    {
      $sql = preg_replace($a, $b, $sql);
      $this->run($sql) or
        error('Unable to run script '.$key."\n".$sql."\n".'. Please check your settings and try again.',  __FILE__, __LINE__, $this->error());
    }
    $this->commit_retain();
  }
}

/**
  command_class
  
*/

class command_class
{
  var $handle;
  var $result;
  var $insert_id;
  var $session;
  var $connection;
  var $prepared = false;
  var $fetch_blobs = false;
  var $fields;//result of fetch
  var $sql = '';

  function __construct() {
  }

  function __destruct() {
    $this->close();
  }

  /**
  * $cond is array of conditions
  */
  public function add_where($cond, $equal = '=') {
    $sql = '';    
    foreach ($cond as $name => $value) {
      if (!empty($value)) {
        if (empty($sql))
          $sql .= " where ";
        else
          $sql .= " and ";
        if (is_numeric($value)) {
          $sql .= "(" .$name .$equal .$value .")";
        } else if (is_string($value)) {
          $sql .= "(" .$name .$equal ."'" .$value ."'" .")";
        }        
      }
    }
  }
  
  public function add_where_ex($cond) {
    $sql = '';    
    foreach ($cond as $where) {

      if (!empty($where->value)) {
        if (empty($sql))
          $sql .= " where ";
        else
          $sql .= " and ";
        if (is_numeric($where->value)) {
          $sql .= "(" .$where->name.' '.$where->compare.' '.$where->value .")";
        } else if (is_string($where->value)) {
          $sql .= "(" .$where->name.' ' .$where->compare .' ' ."'".$where->value ."'" .")";
        }        
      }
    }
    if (!empty($sql))
      $this->sql .= ' '.$sql;
  }

  public function prepare($sql = '')
  {
    if (!empty($sql))
      $this->sql = $sql;
      
    $this->handle = $this->do_prepare();
    $this->prepared = true;//or check if good prepared
    return $this->prepared;
  }

  public function query($sql = '', $field_id = null)
  {
    if (empty($sql))
      $sql = $this->sql;

    $this->prepare();
    $this->handle = $this->do_execute($sql, false, $field_id);
    return $this->handle;
  }

  public function fetch()
  {
    $r = $this->do_fetch();
    $this->eof = empty($r);
    return $r;
  } 
  
  public function fetch_all($fetch_as = as_object)
  {
    return $this->do_fetch_all($fetch_as);
  }

  public function execute($next_when_execute = true, $params = null)
  {
    if ($this->prepared===false)
      $this->prepare($this->sql);

    $this->eof = false;
    $r = $this->do_execute($params);
    if ($r and $next_when_execute) {
      $r = $this->next();
    }
    return $r;
  }

  public function next()
  {
    $this->fields = $this->fetch();
    $this->eof = empty($this->fields);
    return !$this->eof;
  } 

  public function close()
  {
    unset($this->fields);
    $this->eof = false;
    $this->prepared = false;
    return $this->do_close();
  }
  
  public function csv_fields($comma = ';') {
    $r = '';
    foreach ($this->fields as $key => $value) {
      if (!empty($r)) 
        $r = $r.$comma;         
      $r = $r.$value;      
    }    
   return $r;
  }

  public function csv_columns($comma = ';') {
    $r = '';
    foreach ($this->fields as $key => $value) {
      if (!empty($r)) 
        $r = $r.$comma;         
      $r = $r.$key;      
    }    
    return $r;
  }
  
  /**
   * Call back all rows   * 
   * @param function $func($count)
   */
  public function rows_cb($func) {    
    $count = 0;
      while(!$this->eof) {
        $func($count);
      $count++;
      $this->next();
    }
  }
  /**
   * Call back fields and values
   * @param function $func($name, $value)
   */
  public function fields_cb($func, $fields = null) {
    foreach ($this->fields as $name => $value) {
      if (!isset($fields) || in_array($name, $fields))
        $func($name, $value);
    }    
  }

  
}