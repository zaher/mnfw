<?php
  if (!empty($app->req->user)) {
    $app->db->open();
    if ($app->user->login($app->req->user, $app->req->password)) {
      if (empty($app->ref))
        $app->redirect($app->url);
      else
        $app->redirect($app->ref);
      return;
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
<div class="login">
  <form method="post" name="login-form" action=<?php print_quote($app->url.'login?ref='.$app->ref) ?> >
    <p>اسم المستخدم</p>
    <input type="text" name="user" />
    <p>كلمة المرور</p>
    <input type="password" name="password" />
    <input type="submit" value="ولوج" />
  </form>
</div>