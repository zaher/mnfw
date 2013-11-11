<?php
  if (!isempty($_POST['user'])) {
    $app->db->open();
    if ($app->user->login($_POST['user'], $_POST['password'])) {
      $app->redirect($_REQUEST['ref']);
      exit();
    }
    else {
      $err = 'Error login';
    }
  }
?>
<?php
  $app->add_css('controls.css');
  $app->page->title = 'Login Page';
  $app->send_header();
  if (!empty($err)) {
?>
  <p>Error Accord: <?php print $err ?></p>
<?php
  }
?>
<div class="login aligncenter">
  <form method='post' name='login_frm' action=<?php print_quote($app->url.'login?ref='.$app->request_uri) ?> >
    <p>اسم المستخدم</p>
    <input name='user' type='text'/>
    <p>Password</p>
    <input name='password' type='password'>
    <input type=submit value='ولوج' />
  </form>
</div>