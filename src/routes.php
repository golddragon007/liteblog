<?php

function generateRandomString($length = 50) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

require_once 'routes/routes.blogpost.php';
require_once 'routes/routes.install.php';
require_once 'routes/routes.login-out.php';
require_once 'routes/routes.user.php';

// Routes
$app->get('/', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("List every post");

  $this->db;
  $args['posts'] = blogposts::all()->sortByDesc('created_at');

  // Render index view
  return $this->renderer->render($response, 'index.phtml', $args);
});

