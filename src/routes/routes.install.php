<?php

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
