<?php

$app->get('/user/activate', function () use ($app) {
    $code = $app->request->get('code');

    $activationRepository = new Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
    $activation = Cartalyst\Sentinel\Activations\EloquentActivation::where("code", $code)->first();

    if (!$activation) {
        echo "Activation error!";

        return;
    }

    $user = $app->container->auth->findById($activation->user_id);

    if (!$user) {
        echo "User not found!";

        return;
    }


    if (!$activationRepository->complete($user, $code)) {
        if ($activationRepository->completed($user)) {
            echo 'User is already activated. Try to log in.';

            return;
        }

        echo "Activation error!";

        return;
    }

    echo 'Your account has been activated. Log in to your account.';

    return;
});

