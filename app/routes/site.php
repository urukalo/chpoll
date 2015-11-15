<?php



$app->get('/logout', function () use ($app) {
    $app->container->auth->logout();

    echo 'Logged out successfuly.';
});

$app->get('/login', function () use ($app) {
    $app->twig->display('login.html.twig');
});

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

            
        }
    } catch (Cartalyst\Sentinel\Checkpoints\ThrottlingException $ex) {
        echo "Too many attempts!";

        
    } catch (Cartalyst\Sentinel\Checkpoints\NotActivatedException $ex){
        echo "Please activate your account before trying to log in";
        
        
    }
     $app->twig->display('login.html.twig');
});

$app->get('/', function () use ($app) {
    
    $app->twig->display('home.html.twig');
});

$app->get('/register', function () use ($app) {
    
    $app->twig->display('register.html.twig');
});

$app->post('/register', function () use ($app) {
    // we leave validation for another time
    $data = $app->request->post();

    $role = $app->container->auth->findRoleByName('Admin');

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
            'user.delete' => false,
        ],
    ]);

    // attach the user to the admin role
    $role->users()->attach($user);

    // create a new activation for the registered user
    $activation = (new Cartalyst\Sentinel\Activations\IlluminateActivationRepository)->create($user);

    mail($data['email'], "Activate your account", "Click on the link below \n <a href='http://vaprobash.dev/user/activate?code={$activation->code}&login={$user->id}'>Activate your account</a>");
    echo "Please check your email to complete your account registration. (or just use this <a href='http://vaprobash.dev/user/activate?code={$activation->code}&login={$user->id}'>link</a>)";
});


