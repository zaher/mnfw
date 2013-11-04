Mini Framework
==============

##Usage

Put all file into 'fw' subfolder in your site or you can create submodule with git, now in your site you must have 'inc' folder, in that folder add new file main.php and open this file, add simple html code there.

    /fw <- here our framework
    /inc
    index.php  <- copy it from `fw\core\def\init`
    .htaccess  <- copy it from `fw\core\def\init`

    and other folder like
    /css
    /js
    /img

For you index, copy from `fw/core/def/init` to the root folder of your project

If you like to divide your html to header and footer with 'main.php', add in your of 'main.php' this lines into it

    $app->send_header();
    .
    . you body html code here
    .
    $app->send_header();

Framework now send a default html header or footer, but you can add the 'header-html.php' or 'footer.php' in your 'inc' folder to load it instead of the default one

You can send header without sending footer, framework will send it automatically

    $app->send_header();
    .
    . you body html code here

If you want to not send it add this '$app->auto_send = false' at the top of your file.

####Adding new page

Simple add new file 'simple.php' (for example) in you 'inc' folder as same of main, now it is work.

####Config file

Config file will auto loaded config.php, and also functions.php, so you not need to include both, if it exists it will loaded.

Add new 'config.php' in your 'inc' folder, you can look at the default one in 'public\fw\core\def' and copy it.

'config.php' and ;functions.php' and others files have public variables

    $app
    $conf
    $session
    $user

####Send another page

If you need to load another page instead of the current, for example if are in simple.php and need to send login.php just ask $app todo that

    $app->send_page('login');

Do not add .php it is a page not a include file, and the other page not will send header or footer that already sent by original page.

####Send file css/js

Easy before sending header add this lines

    $app->scripts['jquery'] = 'js/jquery.js';
    $app->scripts['main'] = 'js/main.js';
    $app->send_header();

and you can include file through $app

    $app->require_file($app->core_dir.'ui/ui.php');


Example
-------

There is another repository (TODO) show how to use this framework