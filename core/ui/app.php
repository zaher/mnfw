<?php
/**
*  Base class of framework.
*
*/

/**
* http://php.net/manual/en/language.oop5.properties.php
*/

function __($word) {
  return $word;
}

class JS {
  protected $app = null;

  function __construct($app) {
    $this->app = $app;
  }

  function __destruct() {
  }

  public function load_script($name, $script) {
  ?>
  <script type="text/javascript">loadScript(<?php print_quote($name) ?>, <?php print_quote($this->url.$script) ?>, function(){});</script>
  <?php
  }
}

class Database {

  public $connection = null;
  public $session = null;
  protected $app = null;

  function __construct($app) {
    $this->app = $app;
    $app->require_file($app->core_dir.'db/classes.php');
  }

  function __destruct() {
    $this->close();
  }

  public function activate() {
    if (empty($this->connection))
      $this->open();
  }

  public function open() {
    global $app;

    if (!empty($this->connection))
      throw new Exception('Already open');

    $conf = $app->conf['db'];

    $this->connection = new_connection($conf['type'], $conf['database'], $conf['user'], $conf['password'], $conf['host'], $conf['prefix'], false);
    $this->connection->connect();
    $this->session = $this->connection->new_session();
    $this->session->start();
  }

  public function close() {
    global $app;
    if (!empty($this->connection)) {
      $this->session->stop();
      $this->session = null;
      $this->connection->close();
      $this->connection = null;
    }
  }
}

class Page {
  protected $app = null;
  public $title = '';
  public $url = '';
  public $forms = array();

  function __construct($app) {
  }

  function __destruct() {
  }
  
  public function open() {    
  
  }
  
  public function close() {
    
  }
}

class App {
  protected $is_sent = false;
  protected $last_page = ''; //Last page was sent
  public $ref = '';

  public $user = null;//User
  public $styles = array(); //css
  public $scripts = array(); //scripts file
  public $meta = array();

  public $name = '';
  public $title = '';
  public $conf = array();
  public $db = null;
  public $js = null;
  public $page = null;

  public $root = ''; //the root dir of ur application site
  /**
   * Where your html files and template usually "inc" or "app"
   */
  public $app_dir = ''; 
  /**
   *  Framework dir is where the public folder of the framework, if you can muliple project based
   *  On this framework, you can point to it here, 
   *  Core files also detected from here but also you can move the core files to another directory   * 
   */
  public $fw_dir = ''; 
  /**
   *  fw_url The full url your framework the public folder
   * 
   */
  public $fw_url = ''; //url to framework, useful to get css and scripts
  /**
   *  core_dir Where the core of framework found, it is part of framework but you can hide it in another folder
   *  It is auto detect from fw_dir, but you can change it in the setting.php
   * 
   */
  public $core_dir = '';
  /**
  *  When sending run or execute if no page define we will load $default_page
  *
  */
  public $default_page = 'index';

  public $domain = '';
  public $url ='';

  public $redirect_delay = 0;//1.5;

  public $lang = 'en';

  protected $header_sent = false;
  protected $footer_sent = false;
  public $auto_send = true;

/**
  $root: Full path to your site dir
  $app_dir: Full path to direcory of application files may be under the root, file like "app" folder or "inc"
  partial path next to the domain like "sales" that apear under the domain, example www.mydomain.com/sales

*/
  function __construct($root, $app_dir, $title, $name = '') {
    $this->root = inc_delimiter($root); //TODO: we must check if / terminated
    $this->app_dir = inc_delimiter($app_dir);

    $this->url = inc_delimiter(dirname($_SERVER['SCRIPT_NAME']));

    $this->core_dir = inc_delimiter(realpath(__DIR__.'/../'));
    $this->fw_dir = inc_delimiter(realpath(__DIR__.'/../../'));
    $this->fw_url = inc_delimiter($this->url.basename($this->fw_dir));

    $this->title = $title;
    $this->name = $name;
    
    $this->safe_include($this->fw_dir.'/setting.php');
    $this->domain = $_SERVER['SERVER_NAME'];

    $this->meta['Content-Type'] = 'text/html; charset=utf-8';

    $this->safe_use('config');

    $this->init();

  }

  function __destruct() {
    if (isset($this->page))
      $this->page->close();
  }

  protected function init() {
    //not needed but for backlegacy
    define('_APP_', $this->app_dir);
    define('_ROOT_', $this->fw_dir);
    
    //auto detect style
    if (file_exists($this->root.'style.css')) {
      $this->styles['style'] = $url.'style.css';
    } else if (file_exists($this->root.'css/style.css')) {
      $this->styles['style'] = $url.'css/style.css';
    }

    $this->scripts['fw_script'] = $this->fw_url.'js/script.js';

    if (file_exists($this->root.'scripts.js')) {
      $this->scripts['app_script'] = $url.'script.js';
    } else if (file_exists($this->root.'js/script.js')) {
      $this->scripts['app_script'] = $url.'js/script.js';
    }

    $this->js = new JS($this);
    $this->db = new Database($this);
    $this->user = new User($this);
    $this->page = new Page($this);

    if (isempty($this->conf['type']))
      $this->conf['type'] = 'mysql';

    if (isempty($this->conf['prefix']))
      $this->conf['prefix'] = '';

    $this->lang = $this->conf['lang'];

  }

  public function make_globals() {
    $GLOBALS['conf'] = &$this->conf;
    $GLOBALS['user'] = &$this->user;
    $GLOBALS['db'] = &$this->db;
    $GLOBALS['connection'] = &$this->db->connection;
    $GLOBALS['session'] = &$this->db->session;
  }

  /**
   * Require once the file, not safe.
   * It needs full path to the file.
   */
  public function require_file($name) {
    global $app;
    require_once($name);
  }

  /**
   * Include the file if exists.
   * It needs full path to the file.
   */
  public function safe_include($name) {
    global $app;
    if (file_exists($name))
      include($name);
  }
  
  protected function get_use_file($name, $fall = true) {
    $f = $this->app_dir.$name.'.php';
    if ($fall && !file_exists($f)) {
      $f = $this->core_dir.'/def/'.$name.'.php';
      if (!file_exists($f)) {
        $f = '';
      }
    }
    return $f;
  }

  public function use_file($name) {
    $this->make_globals();
    $user = &$this->user;
    $session = &$this->session;
    global $app;
    global $session;
    global $user;
    global $conf;

    include($name);
  }

  public function safe_use($name) {
    global $app;
    $conf = &$this->conf;

    $f = $this->get_use_file($name);

    if (!empty($f)) {
      include($f);
      return true;
    }
    else
      return false;
  }

  /**
  *  Send the page file and fall into 404 page if not exists
  */
  public function safe_send($name, $fall = true) {
    $f = $this->get_use_file($name);
    if ($fall && (empty($f) || (!file_exists($f)))) {
      $f = $this->get_use_file('404');
    }

    if (!empty($f)) {
      $this->make_globals();
      $user = &$this->user;
      $session = &$this->session;
      global $app;
      global $session;
      global $user;
      global $conf;
      include($f);
    }
  }

  public function send_meta() {
    foreach ($this->meta as $name => $value) {
    ?>
  <meta http-equiv=<?php print_quote($name) ?> content=<?php print_quote($value) ?> />
    <?php //TODO: bug in miniedit
    }
  }

  public function send_styles() {
    foreach ($this->styles as $name => $value) {
    ?>
  <link rel='stylesheet' id=<?php print_quote($name) ?> href=<?php print_quote($value) ?> type='text/css' media='all' />
    <?php //TODO: bug in miniedit
    }
  }

  public function send_scripts() {
    foreach ($this->scripts as $name => $value) {
    ?>
  <script language="javascript" id=<?php print_quote($name) ?> src=<?php print_quote($value) ?>></script>
    <?php //TODO: bug in miniedit
    }
  }

/**
*  Send Header and Footer
*/

  public function send_header($force=false) {
    if ($force || !$this->header_sent) {
      $this->header_sent = true; //Here to prevent the loop, now it is send
      $this->safe_send('header.html');
    }
  }

  public function send_footer($force=false) {
    if ($force || !$this->footer_sent) {
      $this->footer_sent = true; //Here to prevent the loop
      $this->safe_send('footer.html');
    }
  }

/**
*   Send Full Html or one Page
*/

  public function send_html($page = '') {
    if (empty($page))
      $page = $this->default_page;

    $this->last_page = $page;
    $this->ref = $page;
    $this->page->url = $this-> $this->url.$page;

    $this->safe_require('functions');
    $this->send_header();

    try {
      $this->safe_send('top.html');
      $this->safe_send($page);
      $this->safe_send('bottom.html');
    } catch (Exception $e) {
      echo htmlentities($e->getMessage(), ENT_HTML5);
      $this->send_footer();
    }
//    finally { }
    $this->send_footer();
  }

/**
*  Send one page
*  Send the page without sending header, but it will send the footer of auto_send is true and the header was send before
*/

  public function send_page($page = '') {
    if (empty($page))
      $page = $this->default_page;

    $this->safe_use('functions');

    try {
      $this->safe_send($page);

    } catch (Exception $e) {
      echo htmlentities($e->getMessage(), ENT_HTML5);
      $this->safe_footer();
    }

    if ($auto_send) {
      if ($this->header_sent) //Yes we checking header, if not we will not auto send footer
        $this->send_footer();
    }
  }

/**
*
*/

  public function send_action($page = '') {
    if (empty($page)) {
      $page = $this->default_page;
    }

    $this->last_page = $page;

    if (!$this->header_sent) {
      $this->safe_send('header');
      $this->safe_use('functions');
      $this->header_sent = true;
    }

    $this->safe_send($page);

    if (!$this->footer_sent) {
      $this->safe_send('footer');
      $this->footer_sent = true;
    }
  }

  public function get_ref() {
    if (!empty($this->ref))
      return $this->ref;
    else
      return $_POST['ref'];
  }

  public function redirect($redirect_url) {

    if (empty($redirect_url))
       $redirect_url = $_SERVER['PHP_SELF'];


    if ($this->redirect_delay == 0)
    {
       header('Location: '.str_replace('&amp;', '&', $redirect_url));
       exit;
    }
    else
    {
//      $this->safe_send('redirect', 'html');
    }
  }

  public function set_cookie($name, $value) {
    setcookie($name, $value, $this->cookie_expire, $this->url, $this->domain);
  }

  //Run will send the header footer for page
  public function run() {
    $this->send_html($_GET['id']);
  }

  public function execute() {
    $this->send_page($_GET['id']);
  }

  public function get_page_title() {
    $title = $this->title;
    if (!empty($this->page->title))
      $title = $title.$this->page->title;
    return $title;
  }

  public function print_page_title() {
    print $this->get_page_title();
  }
}

/**
*
*
*/

define('LEVEL_NORMAL', 0);
define('LEVEL_GUEST', 1);
define('LEVEL_USER', 2);
define('LEVEL_MODERATOR', 3);
define('LEVEL_ADMIN', 4);

function check_level($this_level, $level)
{
//if (($addon['level'] <= $level) and (($addon['level'] <> LEVEL_GUEST) or ($level < LEVEL_USER)))
  $ok = ($this_level <> LEVEL_GUEST) && ($this_level <= $level);
  $ok = $ok || (($this_level == LEVEL_GUEST) && ($level <= LEVEL_GUEST));
  return $ok;
}

class User {
  var $id;
  var $hash;
  var $name;
  var $title;
  var $group;
  var $is_admin = false;
  var $is_moderator = false;
  var $is_user = false;
  var $is_guest = false;
  var $level = 0;

  var $options = array();
  var $rights = array();
  var $groups = array();

  protected $app = null;

  function __construct($app) {
    $this->app = $app;

    $this->level = LEVEL_GUEST;
    $this->is_admin = false;
    $this->is_moderator = false;
    $this->is_user = false;
    $this->is_guest = true;
  }

  public function hash($name, $password)
  {
    return sha1($name.','.$password);
  }

  //Load params to the session security, or it just check
  public function login($username, $password)
  {
    global $app;
    $app->make_globals();

    $ok = do_login(true, false, $username, $password); //found in functions.php
    if ($ok)
    {
      $app->set_cookie('cookie[userid]', $this->id);
      $app->set_cookie('cookie[userhash]', $this->hash);
    }
    return $ok;
  }

  function logout()
  {
    //do_logout();
    global $app;
    $app->set_cookie('cookie[userid]', '');
    $app->set_cookie('cookie[userhash]', '');
    $this->level = LEVEL_GUEST;
    $this->is_admin = false;
    $this->is_moderator = false;
    $this->is_user = false;
    $this->is_guest = true;
    unset($this->id);
    unset($this->name);
    unset($this->title);
    $options = array();
    $rights = array();
    $group = array();
  }

  function check_user($username, $password)
  {
    return do_login(false, $username, $password);
  }

  public function check($level = LEVEL_GUEST)
  {
    global $app;
    $this->level = LEVEL_GUEST;
    if (isset($_COOKIE['cookie']))
    {
      $cookie = $_COOKIE['cookie'];
      $userid = $cookie['userid'];
      $userhash = $cookie['userhash'];
      $ok = do_login(true, true, $userid, $userhash);
      if (!$ok) {
        $app->set_cookie('cookie[userid]', '');
        $app->set_cookie('cookie[userhash]', '');
      }
      else
      {
        if ($this->is_admin)
          $this->level = LEVEL_ADMIN;
        else if ($this->is_moderator)
          $this->level = LEVEL_MODERATOR;
        else
          $this->level = LEVEL_USER;
        $this->is_user = true;
      }
      return $ok;
    }
    else
    {
      $app->safe_send('login');
    }
    if (!check_level($level, $this->level))
    {
//        print_div_error('Not your page :(');
        die('Not your page :P');
    }
  }
}

/**
*
*/

function get_request($name, &$value, $default = '')
{
  if (array_key_exists($name, $_REQUEST))
  {
    $value = $_REQUEST[$name];
    return true;
  }
  else
  {
    $value = $default;
    return false;
  }
}

function get_value($array, $name, &$value)
{
  if (array_key_exists($name, $array))
  {
    $value = $array[$name];
    return true;
  }
  else
  {
    $value = '';
    return false;
  }
}

function redirect($redirect_url)
{
  if (empty($redirect_url))
     $redirect_url = $_SERVER['PHP_SELF'];

  if (!defined('redirect_delay'))
    $redirect_delay = 0;
  else
    $redirect_delay = redirect_delay;

  $redirect_url=str_replace('&amp;', '&', $redirect_url);
  if ($redirect_delay == 0)
  {
    header('Location: '.str_replace('&amp;', '&', $redirect_url));
    exit;
  }
  else
  {
  }
}

function inc_delimiter($s)
{
  if (substr($s, -1)!=='/')
    $s = $s.'/';
  return $s;
}

function exl_delimiter($s)
{
  if (substr($s, -1)!=='/')
    $s=substr($s, 0, -1);
  return $s;
}

function isempty(&$value){
  return !isset($value) or empty($value);
}

function connect_str($a, $seprator, $b){
  if (!isempty($a))
    $a = $a.$seprator;
  $a = $a.$b;
  return $a;
}

function url_page($page, $params = null, $dir = null, $domain = null)
{
  $r = inc_delimiter($domain);
  if (!isempty($dir))
    $r = $r.inc_delimiter($dir);
  if (use_url_page)
    $r = $r.inc_delimiter($dir);

  if (!isempty($page))
  {
    if (use_url_page)
      $r = $r.$page;
    else
      $r = $r.'?'.'id='.$page;
  }

  if (!isempty($params))
  {
    if ((use_url_page) or (isempty($page)))
    {
      $r = $r.'?';
      $r = $r.$params;
    }
    else
      $r = $r.'&'.$params;
  }
  return $r;
}

?>