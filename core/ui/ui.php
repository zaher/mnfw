<?php
/**
* Render create the object and pass attributes to it
* $value is an attrubute of this object but you can pass render function instead of it
*/

$form = null;

function render($class, $value = null, $func = null) {
  if (is_array($value))
    $attributes = $value;
  else
    $attributes = null;

  $object = new $class($attributes);

  if (is_callable($value)) {
    $value($object);
  }

  if (is_callable($func))
    $object->render_func = $func;
  $object->render();
}

function print_quote($v, $q='"') {
  print $q.$v.$q;
}

/**
* Test the value then print "value" with quote
* Example: print_param(', ', $value)
*/

function print_param($before, $v, $force = false, $q = '"') {
  if ($force || isset($v))
    print $before.$q.$v.$q;
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

/**
* Same above but add space before printing
*/
function _print_value($name, $value, $q='"') {
  if (isset($value) && !empty($value))
    print " ";
  print_value($name, $value, '', $q);
}

/**
*   Base Classes
*/

  class View {

    protected $app = null;
    public $parent = null;

    public $attributes = array();
    public $render_func = null;

    function __construct($attributes) {

      if (isset($attributes))
        $this->attributes = $attributes;

/*    //another way, we will not use it.
      foreach($attributes as $attribute => $value) {
        $this->$attribute = $value;
      }*/

      if (method_exists($this, 'init')) //only for user define classes in his project
      {
        $new = $this->init();
        if (isset($new)) {
          $this->attributes = array_merge($this->attributes, $new);
        }
      }
    }

    function __destruct() {
    }


    function __call($method, $args) {

      if(is_callable($this->methods[$method]))
      {
        return call_user_func_array($this->methods[$method], $args);
      }
    }
/*
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

    public function call($func) {
    /* not now
      $f = \Closure::bind($func, $this, get_class());
      $f();
      */
      $func();
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

      $render_func = $this->render_func;
      if (is_callable($render_func))
        $render_func($this);

      if (method_exists($this, 'do_render'))
        $this->do_render();

      $this->close();
    }

    public function process() {
      $this->do_process();
    }

    public function get_name() {
      global $app;
      if (isset($this->name)) {
        $name = $this->name;
        if (isset($app->page->form))
          $name = 'form['.$name.']';
      }
      return $name;
    }
  }

/**
*  Form Class
*/

  class FormView extends View {

    public $requires = array();

    function need_script(){
      return sizeof($this->requires) > 0;
    }

    function generate_requires() {
      $i = 0;
    ?>
        var requires= new Object();
        <?php if(sizeof($this->requires) > 0) { ?>
        requires.fields = new Array();
        <?php foreach($this->requires as $v) { ?>
        requires.fields[<?php print($i); $i++ ?>] = <?php print_quote($v) ?>;
     <?php
        }
      }
    }

    function generate_script() {
      ?> attachForm(<?php print_quote('#'.$this->id);?>, requires); <?php
    }

    function get_action() {
      $action = $this->action;
      if (isset($this->do)) {
        $action = $action.'?do='.$this->do; //TODO Check for ? or &
      }
      return $action;
    }

    public function do_open() {
      global $app;
      $app->page->form = $this; //TODO check of it is exists

      if (isset($this->label)) {
      ?>
      <label <?php print_value('for', $this->name); ?> > <?php print $this->label; ?></label>
      <?php }  ?>
      <form <?php
        print_value('method', $this->method);
        _print_value('name', $this->name);
        _print_value('id', $this->id);
        _print_value('action', $this->get_action()); ?>>
      <?php
    }

    public function do_close() {
      global $app;
      if (isset($this->submit)) {
      ?>
      <input type="submit" <?php print_value('value', $this->submit); ?> />
      <?php }
        if ($this->need_script()) {
      ?>
      <script>
      <?php $this->generate_requires(); ?>
      <?php $this->generate_script(); ?>
      </script>
       <?php
       }
       ?>
      </form>
    <?php
      $app->page->form = null;
    }
  }

/** Ajax form */

  class AjaxFormView extends FormView {

    function need_script(){
      return true;
    }

    function generate_script() {
    ?>
      ajaxAttachForm(<?php
        print_quote('#'.$this->id, true);
        print_param(', ', $this->do, true);
        ?>, requires <?php
        print_param(', ', $this->container);
      ?>)
    <?php
    }

    function get_action() {
      return $this->action;
    }

    public function do_open() {
    ?>
    <div>
    <div class="error-panel" style="display:none">Error</div>
    <?php
      parent::do_open();
    }
    public function do_close() {
      parent::do_close();
//    print_r($this->requires);
/*    if (isset($this->container)) {
    ?>
    <div <?php print_value("id", $this->container) ?> class="form-content"></div>
    <?php
    } */
    ?>
    </div>
    <?php
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
      <select id=<?php print_quote($this->name) ?> name=<?php print_quote($this->get_name()) ?>>
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
      global $app;
      if ($this->is_require) {
        if (isset($app->page->form))
          $app->page->form->requires[] = $this->name;
      }
      if (!empty($this->label)) {
      ?>
      <label <?php print_value('for', $this->id); ?>> <?php print $this->label; ?></label>
      <?php }
        if (isset($this->type))
          $type = $this->type;
        else
          $type = 'text';
      ?>
      <input <?php print_value('type', $type); _print_value('class', $this->class); _print_value('id', $this->id); _print_value('name', $this->get_name()); _print_value('value', $this->value); ?> />
      <?php
    }
  }

/**
*  Functions
*/

function OpenDiv($class = '', $id='') {
  print('<div'); _print_value('class', $class); _print_value('id', $id); print('>');
}

function CloseDiv() {
  print('</div>');
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