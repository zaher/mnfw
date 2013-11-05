<?php
  include_once(__DIR__.'/fw/fw.php');

  $app = new App(__DIR__, __DIR__.'/inc', 'My Application');
  $app->default_page = 'main';
  //Optional paths
  //$app->url_dir = Optional, it can be detected from the root
  //$this->fm_dir = Where the framework exists, it is autodetect
  //$this->fm_url = Where the framework url exists, needed for default css and js

  //$app->run(); //It will send the full html of just the page, or you can use execute
  $app->execute(); //With "execute" you need to send_header manually in each page
?>