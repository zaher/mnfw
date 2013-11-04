<?php
/**
 * MNFW/DB
 *
 * @license   The MIT License (MIT) (http://opensource.org/licenses/MIT)
 * @author    Zaher Dirkey <zaher at parmaja dot com>
 * @author    Jihad Khalifa <jihad at parmaja dot com>
 *
 **/

function append_and_number(&$where, $field, $value, $equal = "=") {
  if ($value != '') {
    if ($where != "")
      $where .= " and ";
    $where .= "(" .$field .$equal .$value .")";
  }
}

function append_and_data(&$where, $field, $value, $equal = "=") {
  if (($value != '') && ($value != 0)) {
    if ($where != "")
      $where .= " and ";
    $where .= "(" .$field .$equal .$value .")";
  }
}

function append_and_str(&$where, $field, $value, $equal = "=") {
  if ($value != '') {
    if ($where != "")
      $where .= " and ";
    $where .= "(" .$field .$equal ."'" .$value ."'" .")";
  }
}

function append_and_date(&$where, $field, $value, $equal = "=") {
  append_and_str($where, $field, $value, $equal);
}
?>