<?php

// Login.
$app->get('/login', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("View login");

  if (!empty($_SESSION['user'])) {
    return $response->withRedirect('/');
  }

  $args['token'] = generateRandomString();
  $_SESSION['form_tokens'][] = $args['token'];

  // Render index view
  return $this->renderer->render($response, 'login.phtml', $args);
});

// Login.
$app->post('/login', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("View login process");

  $this->db;

  if (!empty($_SESSION['user'])) {
    return $response->withRedirect('/');
  }

  $token_key = array_search($_REQUEST['token'], $_SESSION['form_tokens']);
  if ($token_key === FALSE) {
    $args['errors'][] = "Token missmatch! Operation aborted!";
  }

  if ($token_key !== FALSE) {
    unset($_SESSION['form_tokens'][$token_key]);
  }
  $args['token'] = generateRandomString();
  $_SESSION['form_tokens'][] = $args['token'];

  $user = NULL;
  if (empty($args['errors'])) {
    $user = user::query()
      ->where('username', '=', $_REQUEST['username'])
      ->where('password', '=', hash('sha512', $_REQUEST['password'] . $this->get('settings')['security_token']))
      ->first();
  }

  if (!empty($user)) {
    $_SESSION['id'] = $user->getAttribute('id');
    $_SESSION['username'] = $user->getAttribute('username');
    $_SESSION['email'] = $user->getAttribute('email');
  }
  else {
    $args['errors'][] = 'Wrong Username or Password!';
  }

  if (!empty($args['errors'])) {
    // Render index view
    return $this->renderer->render($response, 'login.phtml', $args);
  }
  else {
    return $response->withRedirect('/');
  }
});

// Logout.
$app->get('/logout', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("View login");

  unset($_SESSION['id'], $_SESSION['username'], $_SESSION['email']);

  // Render index view
  return $response->withRedirect('/');
});
