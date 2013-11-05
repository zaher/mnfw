<?php
  function render($class, $attributes, $render_func = null) {
    $object = new $class($attributes);
    $object->render_func = $render_func;
    $object->render();
  }

function print_quote($v, $q='"') {
  print $q.$v.$q;
}

/**
* Test the value then print name="value" with quote
* Useful for generate HTML
*/

function print_value($name, $value, $extra = '', $q='"')
{
  if (isset($value) && !empty($value))
  {
    if (!empty($name))
      print($name.'=');
    print_quote($value.$extra);
  }
}

/** Same above but add space before printing
*/
function _print_value($name, $value, $q='"') {
  if (isset($value) && !empty($value))
    print " ";
  print_value_($name, $value, $q);
}



/**
*   Base Classes
*/

  class View {
    protected $app = null;

    public $attributes = array();
    public $render_func = null;

    function __construct($attributes) {

      $this->attributes = $attributes;

/*    //another way, we will not use it.
      foreach($attributes as $attribute => $value) {
        $this->$attribute = $value;
      }*/

      if (method_exists($this, 'init')) //only for user define classes in his project
        $this->init();
    }

    function __destruct() {
    }

    /**
    *  http://php.net/manual/en/language.oop5.overloading.php
    */
    public function __set($name, $value) {
      $this->attributes[$name] = $value;
    }

    public function &__get($name) {
      return $this->attributes[$name];
    }

    public function __isset($name)
    {
      return array_key_exists($name, $this->attributes);
    }

    public function __unset($name)
    {
      unset($this->attributes[$name]);
    }

    function __toString() {
      //TODO
    }

    public function open() {
      if (method_exists($this, 'do_open'))
        $this->do_open();
    }

    public function close() {
      if (method_exists($this, 'do_close'))
        $this->do_close();
    }

    public function render() {
      $this->open();
      if (method_exists($this, 'do_render'))
        $this->do_render();
      if (isset($this->render_func))
        $this->render_func();
      $this->close();
    }

    public function process() {
      $this->do_process();
    }

    public function render_controls() {
//      $this->do_render_controls();
    }
  }

  class Form extends View {

    public function do_open() {
    ?>
      <form method=<?php print_quote($method) ?> name=<?php print_quote($this->name) ?> action=print_quote($this->action) >
    <?php
    }

    public function do_close() {
    ?>
      </form>
    <?php
    }

    protected function do_render() {
      $this->open();
      $this->render_controls();
      $this->close();
    }

    protected function do_process() {
    }
  }

/**
*  UI classes
*/

  class SelectView extends View {

    public function do_render() {

      if (isset($this->label)) {
      ?>
      <label for=<?php print_quote($this->name); ?> > <?php print $this->label; ?></label>
      <?php }
      ?>
      <select id=<?php print_quote($this->name) ?> name=<?php print_quote($this->name) ?>>
      <?php
        if ($this->add_empty) {
          print "<option value=''></option>";
        }
        if (isset($this->values)) {
          foreach($this->values as $id => $value)
            print "<option value='".$id."'>".$value."</option>";
      ?>
      </select>
      <?php
        }
    }
  }

  class InputView extends View {

    public function do_render() {
      if (!empty($this->label)) {
      ?>
      <label <?php print_value('for', $this->id); ?>> <?php print $this->label; ?></label>
      <?php }
        if (isset($this->type))
          $type = $this->type;
        else
          $type = 'text';
      ?>
      <input <?php print_value('type', $type); _print_value('class', $this->class); _print_value('id', $this->id); _print_value('id', $this->id); _print_value('value', $this->value); ?> />
      <?php
    }
  }

  /**
  *  $values is array, if you get it from PDO use PDO::FETCH_KEY_PAIR
  */
  function print_select($name, $values, $attribs) {
    $label = $attribs['label'];
    $class = $attribs['class'];
    $selected = $attribs['selected'];
    $empty = $attribs['empty'];
    if (isset($label)) {
    ?>
    <label for=<?php print_quote($name); ?> > <?php print $label; ?></label>
    <?php } ?>
    <select id=<?php print_quote($name) ?> name=<?php print_quote($name) ?>>
    <?php
      if ($empty) {
        print "<option value=''></option>";
      }
      if (isset($values)) {
      foreach($values as $id => $value)
        print "<option value='".$id."'>".$value."</option>";
    ?>
    </select>
  <?php
    }
  }
  ?>