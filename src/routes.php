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

// Routes

$app->get('/', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("List every post");

  $this->db;
  $args['posts'] = blogposts::all()->sortByDesc('created_at');

  // Render index view
  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/install', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Install");

  $args['token'] = generateRandomString();
  $_SESSION['form_tokens'][] = $args['token'];

  // Render index view
  return $this->renderer->render($response, 'start_install.phtml', $args);
});

$app->post('/install', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Install success");

  $token_key = array_search($_REQUEST['token'], $_SESSION['form_tokens']);
  if ($token_key === FALSE) {
    $args['errors'][] = "Token missmatch! Operation aborted!";
  }

  if ($token_key !== FALSE) {
    unset($_SESSION['form_tokens'][$token_key]);
  }

  if (empty($_REQUEST['username'])) {
    $args['errors'][] = "You need to set the Username.";
  }

  if (empty($_REQUEST['password'])) {
    $args['errors'][] = "You need to set the Password.";
  }

  if (empty($_REQUEST['email'])) {
    $args['errors'][] = "You need to set the Email.";
  }

  if (empty($args['errors'])) {
    $this->db;
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule::schema()->dropIfExists('blogposts');
    $capsule::schema()
      ->create('blogposts', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->increments('id');
        $table->string('title');
        $table->text('summary');
        $table->text('body');
        $table->text('author');
        // Include created_at and updated_at
        $table->timestamps();
      });

    /**
     *** Don't beleve for the documentation, it's lieing. ***
     *** https://laravel.com/docs/5.3/eloquent#defining-models ***
     * $cont = new blogposts();
     * $cont->title = "Hello new user!";
     * $cont->summary = ;
     * $cont->body = "The installation was successful, if you see this message.";
     * $cont->author = "System";
     * $cont->created_at = date("Y-m-d H:i:s");
     * $cont->save();
     *
     * $cont = new blogposts();
     * $cont->title = "How to create new content";
     * $cont->summary = "You can read here how can you create new content";
     * $cont->body = "Just go to the main page, where the blog posts are listed, and there is a New blogpost button for it. Fill the form than submit it and done.";
     * $cont->author = "System";
     * $cont->created_at = date("Y-m-d H:i:s");
     * $cont->save();
     *
     * $cont = new blogposts();
     * $cont->title = "Delete or update a post!";
     * $cont->summary = "Description to how to delete or update a post";
     * $cont->body = "Go to tha post lister page, than select that post, witch you want to delete or read. At the post detail page you can find a delete and an update button.";
     * $cont->author = "System";
     * $cont->created_at = date("Y-m-d H:i:s");
     * $cont->save();
     */

    blogposts::create(array(
      'title'      => "Hello new user!",
      'summary'    => "This is your new site!",
      'body'       => "The installation was successful, if you see this message.",
      'author'     => "System",
      'created_at' => date("Y-m-d H:i:s"),
    ));

    blogposts::create(array(
      'title'      => "How to create new content",
      'summary'    => "You can read here how can you create new content",
      'body'       => "Just go to the main page, where the blog posts are listed, and there is a New blogpost button for it. Fill the form than submit it and done.",
      'author'     => "System",
      'created_at' => date("Y-m-d H:i:s"),
    ));

    blogposts::create(array(
      'title'      => "Delete or update a post!",
      'summary'    => "Description to how to delete or update a post",
      'body'       => "Go to tha post lister page, than select that post, witch you want to delete or read. At the post detail page you can find a delete and an update button.",
      'author'     => "System",
      'created_at' => date("Y-m-d H:i:s"),
    ));

    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule::schema()->dropIfExists('users');
    $capsule::schema()
      ->create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->increments('id');
        $table->string('username');
        $table->text('password');
        $table->text('email');
        // Include created_at and updated_at
        $table->timestamps();
      });

    user::create(array(
      'username' => $_REQUEST['username'],
      'password' => hash('sha512', $_REQUEST['password'] . $this->get('settings')['security_token']),
      'email' => $_REQUEST['email'],
    ));

    // Render index view
    return $this->renderer->render($response, 'install.html', $args);
  }
  else {
    return $this->renderer->render($response, 'start_install.phtml', $args);
  }
});

// Blog post add.
$app->get('/blogpost/add', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Add new blog post");

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

// Delete user.
$app->get('/user/{id}/delete', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Delete " . $args['id'] . " id user");

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
  if (empty($args['password'])) {
    $args['errors'][] = "Password is required!";
  }
  if (empty($args['email'])) {
    $args['errors'][] = "Email is required!";
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
  if (empty($args['email'])) {
    $args['errors'][] = "E-mail is required!";
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

  $this->db;
  $args['users'] = user::all(array('id', 'username', 'email'));

  // Render index view
  return $this->renderer->render($response, 'users.phtml', $args);
});
