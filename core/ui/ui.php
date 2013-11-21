<?php

/**
*   Base Classes
*/

/**
*  Form Class
*/
  class ContainerView extends View {
    public $views = array(); //For postpond render object

  }

  class FormView extends ContainerView {

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
        _print_value('class', $this->class);
        _print_value('action', $this->get_action()); ?>>
      <?php
    }

    public function do_close() {
      global $app;
      if (isset($this->submit)) {
      ?>
      <input type="submit" <?php print_value('value', $this->submit); ?> />
      </form>
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
//      parent::generate_script();
    ?>

      ajaxAttachForm(<?php
        print_quote('#'.$this->id);
        print_param(', ', $this->do, true);
        ?>, requires<?php
        print_param(', ', '#'.$this->container);
      ?>);
    <?php
    }

    function get_action() {
      return $this->action;
    }

    public function do_open() {
    ?>
    <div>
    <?php
      parent::do_open();
      ?>
      <p class="message-panel" style="display:none"></p>
      <?php
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
      <select id=<?php print_quote($this->id) ?> name=<?php print_quote($this->get_name()) ?>>
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

  class TableView extends View {

    public function do_open() {
      if (isset($this->label)) {
      ?>
      <label <?php print_value('for', $this->name); ?> > <?php print $this->label; ?></label>
      <?php }  ?>
      <table <?php
        print_value('name', $this->name);
        _print_value('class', $this->class);
        _print_value('id', $this->id);
        _print_value('', $this->get_action()); ?>>
      <?php

    }

    public function do_close() {
      ?>
      </table>
      <?php
    }

    public function do_render() {

    }
  }

/**
*  Ref: http://botmonster.com/jquery-bootpag
*/
  class PaginationView extends View {

    public function created(){
      global $app;
      $app->need('jquery');
      //$this->app->need('components');
    }


    public function do_render() {

      if (isset($this->label)) {
      ?>
      <label for=<?php print_quote($this->name); ?> > <?php print $this->label; ?></label>
      <?php }
      ?>
      <div class="pagination" id=<?php print_quote($this->id) ?> name=<?php print_quote($this->get_name()) ?>></div>
      <script>
        $('.pagination').bootpag({
          total: <?php print($this->total) ?>
          <?php _print_value("maxVisible", $this->max, '', ":", ", ") ?>
            }).on("page", function(event, num){
            <?php
              if (isset($this->post)) {
                print($this->post);
                //somthing here to post
              }
            ?>
        });
      </script>
      <?php
    }
  }

/**
*  Functions
*/

function open_div($class = '', $id='') {
  print('<div'); _print_value('class', $class); _print_value('id', $id); print('>');
}

function close_div() {
  print('</div>');
}

/**
*   UI Lib Class
*
*/

class jQueryLib extends Lib {
  public function init() {
    parent::init();
//    $this->scripts['weinre'] = 'http://192.168.0.1:8080/target/target-script-min.js#anonymous';
    if ($this->app->debug)
      $this->app->add_js('jquery', $this->app->fw_url.'lib/jquery.js'); //or
//      $this->app->scripts['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js';
    else
      $this->app->add_js('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
  }
}

class UILib extends Lib {

  function created() {
    parent::created();
    $this->app->need('jquery');
  }

  public function init() {
    parent::init();
//    $this->scripts['weinre'] = 'http://192.168.0.1:8080/target/target-script-min.js#anonymous';
//    $this->scripts['jquery'] = $this->app->fw_url.'js/jquery.js'; //or
    $this->app->scripts['ui'] = 'fw/js/script.js';
    $this->app->scripts['pagination'] = 'fw/lib/jquery.bootpag.js';
  }
}

$app->add_lib('jquery', new jQueryLib($app));
$app->add_lib('ui', new UILib($app));