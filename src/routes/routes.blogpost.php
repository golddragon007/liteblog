<?php

// Blog post add.
$app->get('/blogpost/add', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Add new blog post");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $args['is_new'] = TRUE;
  $args['token'] = generateRandomString();
  $args['title'] = "";
  $args['summary'] = "";
  $args['body'] = "";
  $args['author'] = $_SESSION['username'];
  $args['created_at'] = date("Y-m-d H:i:s");

  $_SESSION['form_tokens'][] = $args['token'];

  // Render index view
  return $this->renderer->render($response, 'blogpost_edit.phtml', $args);
});

// Blog post add.
$app->post('/blogpost/add', function ($request, $response, $args) use ($app) {
  // Sample log message
  $this->logger->info("Add new blog post");

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
  $args['title'] = $_REQUEST['title'];
  $args['summary'] = $_REQUEST['summary'];
  $args['body'] = $_REQUEST['body'];
  $args['author'] = $_REQUEST['author'];
  $args['created_at'] = $_REQUEST['created_at'] ? $_REQUEST['created_at'] : date("Y-m-d H:i:s");
  $_SESSION['form_tokens'][] = $args['token'];

  if (empty($args['title'])) {
    $args['errors'][] = "Title is required!";
  }
  if (empty($args['body'])) {
    $args['errors'][] = "Body is required!";
  }
  if (empty($args['author'])) {
    $args['errors'][] = "Author is required!";
  }

  if (empty($args['errors'])) {
    blogposts::create(array(
      'title' => $_REQUEST['title'],
      'summary' => $_REQUEST['summary'],
      'body' => $_REQUEST['body'],
      'author' => $_REQUEST['author'],
      'created_at' => $_REQUEST['created_at'],
    ));

    return $response->withRedirect('/');
  }
  if (!empty($_REQUEST['back'])) {
    return $response->withRedirect('/');
  }
  else {
    // Render index view
    return $this->renderer->render($response, 'blogpost_edit.phtml', $args);
  }
});

// Blog post edit.
$app->get('/blogpost/{id}/delete', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Delete " . $args['id'] . " id blogpost");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $args['token'] = generateRandomString();
  $_SESSION['form_tokens'][] = $args['token'];

  $post = blogposts::query()->where('id', '=', $args['id'])->first();
  $args['title'] = $post->getAttribute('title');

  // Render index view
  return $this->renderer->render($response, 'blogpost_delete.phtml', $args);
});

// Blog post edit.
$app->post('/blogpost/{id}/delete', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Delete " . $args['id'] . " id blogpost");

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

  $post = blogposts::query()->where('id', '=', $args['id'])->first();
  $args['title'] = $post->getAttribute('title');

  if (empty($args['errors'])) {
    if (!empty($_REQUEST['delete'])) {
      blogposts::destroy($args['id']);
    }

    return $response->withRedirect('/');
  }
  else {
    return $this->renderer->render($response, 'blogpost_delete.phtml', $args);
  }
});

// Blog post edit.
$app->get('/blogpost/{id}/edit', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Edit " . $args['id'] . " id blogpost");

  if (empty($_SESSION['id'])) {
    $this->logger->info("No permission!");
    return $response->withRedirect('/');
  }

  $this->db;

  $post = blogposts::query()->where('id', '=', $args['id'])->first();

  $args['is_new'] = FALSE;
  $args['token'] = generateRandomString();
  $args['title'] = $post->getAttribute('title');
  $args['summary'] = $post->getAttribute('summary');
  $args['body'] = $post->getAttribute('body');
  $args['author'] = $post->getAttribute('author');
  $args['created_at'] = $post->getAttribute('created_at');
  $_SESSION['form_tokens'][] = $args['token'];

  // Render index view
  return $this->renderer->render($response, 'blogpost_edit.phtml', $args);
});

// Blog post edit.
$app->post('/blogpost/{id}/edit', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Edit " . $args['id'] . " id blogpost");

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
  $args['title'] = $_REQUEST['title'];
  $args['summary'] = $_REQUEST['summary'];
  $args['body'] = $_REQUEST['body'];
  $args['author'] = $_REQUEST['author'];
  $args['created_at'] = $_REQUEST['created_at'] ? $_REQUEST['created_at'] : date("Y-m-d H:i:s");
  $_SESSION['form_tokens'][] = $args['token'];

  if (empty($args['title'])) {
    $args['errors'][] = "Title is required!";
  }
  if (empty($args['body'])) {
    $args['errors'][] = "Body is required!";
  }
  if (empty($args['author'])) {
    $args['errors'][] = "Author is required!";
  }

  if (empty($args['errors'])) {
    blogposts::query()->where('id', "=", $args['id'])->update(array(
      'title' => $_REQUEST['title'],
      'summary' => $_REQUEST['summary'],
      'body' => $_REQUEST['body'],
      'author' => $_REQUEST['author'],
      'created_at' => $_REQUEST['created_at'],
      'updated_at' => date("Y-m-d H:i:s"),
    ));
    return $response->withRedirect('/');
  }
  else {
    // Render index view
    return $this->renderer->render($response, 'blogpost_edit.phtml', $args);
  }
});

// Blog post view.
$app->get('/blogpost/{id}', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("View " . $args['id'] . " id blogpost");

  $this->db;
  $args['post'] = blogposts::query()->where('id', '=', $args['id'])->first();

  // Render index view
  return $this->renderer->render($response, 'blogpost.phtml', $args);
});
