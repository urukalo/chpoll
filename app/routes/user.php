<?php

//@todo -- replace 'echo' with slim flash-data (there is some bug now)

$app->get('/user/activate', function () use ($app) {
    $code = $app->request->get('code');

    $activationRepository = new Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
    $activation = Cartalyst\Sentinel\Activations\EloquentActivation::where("code", $code)->first();

    //oh - no activation
    if (!$activation) {
        echo "Activation error!";
        return;
    }

    $user = $app->container->auth->findById($activation->user_id);

    //oh - no user
    if (!$user) {
        echo "User not found!";
        return;
    }


    //activate
    if (!$activationRepository->complete($user, $code)) {
        //activation is fail -- check is allredy done before? 
        if ($activationRepository->completed($user)) {
            echo 'User is already activated. Try to log in.';
            return;
        }

        //ops, cant activate!
        echo "Activation error!";
        return;
    }

    //activated, do login
    echo 'Your account has been activated.';
    $app->container->auth->login($user);

    $app->redirect('/');
    return;
});

$app->get('/user/poll(/:id)', function ($id = null) use ($app) {

    $pollData = urukalo\CH\Poll::where('active', 1)->with('answers')->with('user_polls');
    $user = $this->app->container->auth->check();

    if (!is_null($id)) {
        $pollDataVote = clone $pollData;

        //check is voted
        if ($pollDataVote->whereHas('user_polls', function ($query) {
                    $query->where('user_polls.idUser', $user->id);
                })->find((int) $id)) {

            echo "You can vote only one time";

            //voted! show results if is public
            $pollData->where('public', 1)->find((int) $id);
            $app->twig->display('poll.html.twig', array(
                "poll" => $pollData,
            ));
        } else {
            //have premision to vote?
            if (!$user->hasAccess('poll.vote')) {
                echo "You don't have the permission to vote.";
                return;
            }
            //vote
            $pollData->find((int) $id);
            $app->twig->display('poll-vote.html.twig', array(
                "poll" => $pollData,
            ));
        }
    } else {

        //display all polls -- @todo: separeate voted from new
        $app->twig->display('polls.html.twig', array("poll" => $pollData->get()));
    }
});


