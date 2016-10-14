<?php

// Delete user.
$app->get('/user/{id}/delete', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Delete " . $args['id'] . " id user");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $args['token'] = generateRandomString();
  $_SESSION['form_tokens'][] = $args['token'];

  $post = user::query()->where('id', '=', $args['id'])->first();
  $args['username'] = $post->getAttribute('username');

  if ($args['id'] == 1) {
    return $response->withRedirect('/users');
  }
  else {
    // Render index view
    return $this->renderer->render($response, 'user_delete.phtml', $args);
  }
});

// Delete user.
$app->post('/user/{id}/delete', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Delete " . $args['id'] . " id user");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $token_key = array_search($_REQUEST['token'], $_SESSION['form_tokens']);
  if ($token_key === FALSE) {
    $args['errors'][] = "Token missmatch! Operation aborted!";
  }

  if ($token_key !== FALSE) {
    unset($_SESSION['form_tokens'][$token_key]);
  }
  $args['token'] = generateRandomString();
  $_SESSION['form_tokens'][] = $args['token'];

  $post = user::query()->where('id', '=', $args['id'])->first();
  $args['username'] = $post->getAttribute('username');

  if (empty($args['errors'])) {
    if (!empty($_REQUEST['delete'])) {
      user::destroy($args['id']);
    }

    return $response->withRedirect('/users');
  }
  else {
    return $this->renderer->render($response, 'user_delete.phtml', $args);
  }
});

// Blog post add.
$app->get('/user/add', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Add new user");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $args['is_new'] = TRUE;
  $args['token'] = generateRandomString();
  $args['username'] = "";
  $args['password'] = "";
  $args['email'] = "";

  $_SESSION['form_tokens'][] = $args['token'];

  // Render index view
  return $this->renderer->render($response, 'user_edit.phtml', $args);
});

// Blog post add.
$app->post('/user/add', function ($request, $response, $args) use ($app) {
  // Sample log message
  $this->logger->info("Add new user");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $token_key = array_search($_REQUEST['token'], $_SESSION['form_tokens']);
  if ($token_key === FALSE) {
    $args['errors'][] = "Token missmatch! Operation aborted!";
  }

  if ($token_key !== FALSE) {
    unset($_SESSION['form_tokens'][$token_key]);
  }
  $args['is_new'] = TRUE;
  $args['token'] = generateRandomString();
  $args['username'] = $_REQUEST['username'];
  $args['password'] = $_REQUEST['password'];
  $args['email'] = $_REQUEST['email'];
  $_SESSION['form_tokens'][] = $args['token'];

  if (empty($args['username'])) {
    $args['errors'][] = "Username is required!";
  }
  else {
    $user = user::query()->where('username', '=', $args['username'])->first();
    if (!empty($user)) {
      $args['errors'][] = 'This username is already in use. You need to choose another one!';
    }
  }
  if (empty($args['password'])) {
    $args['errors'][] = "Password is required!";
  }
  if (empty($args['email'])) {
    $args['errors'][] = "Email is required!";
  }
  else {
    $user = user::query()->where('email', '=', $args['email'])->first();
    if (!empty($user)) {
      $args['errors'][] = 'This email is already in use. You need to choose another one!';
    }
  }

  if (empty($args['errors'])) {
    user::create(array(
      'username' => $_REQUEST['username'],
      'password' => $_REQUEST['password'],
      'email' => $_REQUEST['email'],
    ));

    return $response->withRedirect('/users');
  }
  if (!empty($_REQUEST['back'])) {
    return $response->withRedirect('/users');
  }
  else {
    // Render index view
    return $this->renderer->render($response, 'user_edit.phtml', $args);
  }
});

// Blog post edit.
$app->get('/user/{id}/edit', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Edit " . $args['id'] . " id user");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $post = user::query()->where('id', '=', $args['id'])->first();

  $args['is_new'] = FALSE;
  $args['token'] = generateRandomString();
  $args['username'] = $post->getAttribute('username');
  $args['email'] = $post->getAttribute('email');
  $_SESSION['form_tokens'][] = $args['token'];

  // Render index view
  return $this->renderer->render($response, 'user_edit.phtml', $args);
});

// Blog post edit.
$app->post('/user/{id}/edit', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Edit " . $args['id'] . " id user");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $token_key = array_search($_REQUEST['token'], $_SESSION['form_tokens']);
  if ($token_key === FALSE) {
    $args['errors'][] = "Token missmatch! Operation aborted!";
  }

  if ($token_key !== FALSE) {
    unset($_SESSION['form_tokens'][$token_key]);
  }

  $args['is_new'] = FALSE;
  $args['token'] = generateRandomString();
  $args['username'] = $_REQUEST['username'];
  $args['email'] = $_REQUEST['email'];
  $_SESSION['form_tokens'][] = $args['token'];

  if (empty($args['username'])) {
    $args['errors'][] = "Username is required!";
  }
  else {
    $user = user::query()->where('username', '=', $args['username'])->where('id', '<>', $args['id'])->first();
    if (!empty($user)) {
      $args['errors'][] = 'This username is already in use. You need to choose another one!';
    }
  }
  if (empty($args['email'])) {
    $args['errors'][] = "E-mail is required!";
  }
  else {
    $user = user::query()->where('email', '=', $args['email'])->where('id', '<>', $args['id'])->first();
    if (!empty($user)) {
      $args['errors'][] = 'This email is already in use. You need to choose another one!';
    }
  }

  if (empty($args['errors'])) {
    user::query()->where('id', "=", $args['id'])->update(array(
      'username' => $_REQUEST['username'],
      'email' => $_REQUEST['email'],
      'updated_at' => date("Y-m-d H:i:s"),
    ));
    return $response->withRedirect('/users');
  }
  else {
    // Render index view
    return $this->renderer->render($response, 'user_edit.phtml', $args);
  }
});

// List users.
$app->get('/users', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("View users");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;
  $args['users'] = user::all(array('id', 'username', 'email'));

  // Render index view
  return $this->renderer->render($response, 'users.phtml', $args);
});
