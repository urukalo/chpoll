<?php

//@todo -- replace 'echo' with slim flash-data (there is some bug now)
//logout user
$app->get('/logout', function () use ($app) {
    $app->container->auth->logout();

    echo 'Logged out successfuly.';

    $app->redirect('/');
});

//login form
$app->get('/login', function () use ($app) {
    $app->twig->display('login.html.twig');
});

//login
$app->post('/login', function () use ($app) {
    $data = $app->request->post();
    $remember = isset($data['remember']) && $data['remember'] == 'on' ? true : false;

    try {
        if (!$app->container->auth->authenticate([
                    'email' => $data['email'],
                    'password' => $data['password'],
                        ], $remember)) {

            echo 'Invalid email or password.';
        } else {
            echo 'You\'re logged in';
            $app->redirect('/');
        }
    } catch (Cartalyst\Sentinel\Checkpoints\ThrottlingException $ex) {
        echo "Too many attempts!";
    } catch (Cartalyst\Sentinel\Checkpoints\NotActivatedException $ex) {
        echo "Please activate your account before trying to log in";
    }
    $app->redirect('/login');
});

//home
$app->get('/', function () use ($app) {
    $user = $this->app->container->auth->check();

    if ($user) {
        $admin = $this->app->container->auth->inRole('admin');
        if ($admin)
            $app->redirect('/admin/poll');
        else
            $app->redirect('/user/poll');
    } else
        $app->twig->display('home.html.twig');
});

//register form
$app->get('/register', function () use ($app) {
    $app->twig->display('register.html.twig');
});

//register
$app->post('/register', function () use ($app) {
    // we leave validation for another time
    $data = $app->request->post();

    //get user role
    $role = $app->container->auth->findRoleByName('User');

    if ($app->container->auth->findByCredentials([
                'login' => $data['email'],
            ])) {
        echo 'User already exists with this email.';

        return;
    }

    $user = $app->container->auth->create([
        'first_name' => $data['firstname'],
        'last_name' => $data['lastname'],
        'email' => $data['email'],
        'password' => $data['password'],
        'permissions' => [
            'user.delete' => false, //direct premision, override role premision
        ],
    ]);

    // attach the user to the role
    $role->users()->attach($user);

    // create a new activation for the registered user
    $activation = (new Cartalyst\Sentinel\Activations\IlluminateActivationRepository)->create($user);

    mail($data['email'], "Activate your account", "Click on the link below \n <a href='http://chpool.dev/user/activate?code={$activation->code}&login={$user->id}'>Activate your account</a>");
    echo "Please check your email to complete your account registration. (or just use this <a href='http://chpoll.dev/user/activate?code={$activation->code}&login={$user->id}'>link</a>)";
});


