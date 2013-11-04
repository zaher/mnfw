<?php
/**
 * MNFW/DB 
 *
 * @license   The MIT License (MIT) (http://opensource.org/licenses/MIT)
 * @author    Zaher Dirkey <zaher at parmaja dot com>
 *
 **/

if (!extension_loaded ('PDO' ))
  exit('PDO not enabled.');  //it is only PDO classes
 
if (!extension_loaded('pdo_sqlite'))
  exit('SQLite not supported.');
        
require_once('classes.php');

class sqlite_connection_class extends connection_class
{
  function do_connect()
  {
    $this->handle = new PDO('sqlite:'.$this->name);
    if (!is_object($this->handle))
      exit('Unable to open sqlite database '.$this->host.' '.$error);
  }
  
  function do_disconnect()
  {
    empty($this->handle);
    return true;
  }
  
  function do_new_session()
  {
    return new sqlite_session_class();
  }
}

class sqlite_session_class extends session_class
{
  function do_start()
  {
    return $this->connection->handle->beginTransaction();
  }

  function do_commit($retain = false)
  {
    return $this->connection->handle->commit();
  }

  function do_rollback($retain = false)
  {
    return $this->connection->rollback();
  }

  function do_execute($script)
  {
    $sql = $this->new_cmd();
    $sql->execute($script);
    return $sql;
  }

  function do_before_install($script)
  {
  }


  function do_new_command()
  {
    $sql = new sqlite_command_class();
    $sql->session = $this;
    return $sql;
  }  
}

class sqlite_command_class extends command_class
{
  function do_prepare()
  {
    $handle = $this->connection->handle->prepare($this->sql);
    if (!is_object($handle))
      exit('Error in '.$this->sql);
/*    if (isset($field_id)){
      if ($this->fetch())
        $this->insert_id = $results[$field_id];
    return $results;*/
    return $handle;
  }

  function do_execute($params)
  {
    return $this->handle->execute($params);
  }
  
  function do_fetch()
  {
    $r = $this->handle->fetch(PDO::FETCH_ASSOC);
//  $fetch_blobs
    return $r;
  }

  function do_fetch_all($fetch_as){
    if ($fetch_as == as_objects)
      return $this->handle->fetchAll(PDO::FETCH_OBJ);
    else if ($fetch_as == as_array)
      return $this->handle->fetchAll(PDO::FETCH_ASSOC);
    else
      return $this->handle->fetchAll(PDO::FETCH_KEY_PAIR);
  }

  function do_fetch_row($fetch_blobs = false)
  {
    $r = $this->handle->fetch(PDO::FETCH_ASSOC);//still needs
    return $r;
  }
  
  function do_close()
  {
    $this->handle->closeCursor();
    empty($this->handle);
  }
}