<?php

//@todo -- replace 'echo' with slim flash-data (there is some bug now)
//list all users or get form to edit user
$app->get('/admin/user(/:id)', function ($id = null) use ($app) {
    $loggedUser = $app->container->auth->check();

    if (!$loggedUser->hasAccess('user.*')) {
        echo "You don't have the permission to access this page.";
        return;
    }

    if (!is_null($id)) {

        $user = $this->app->container->auth->findById(1);
        $roles = $this->app->container->auth->getRoleRepository()->get();

        $app->twig->display('user-edit.html.twig', array(
            "user-edit" => $user,
            "roles" => $roles
        ));
    } else {
        $users = $this->app->container->auth->getUserRepository()->get();
        dump($users);
        $app->twig->display('users.html.twig', array(
            "users" => $users
        ));
    }
});

//save new or updated user
$app->post('/admin/user', function() use($app) {
    $data = $app->request->post();

    if (!$loggedUser->hasAccess('user.*')) {
        echo "You don't have the permission to access this page.";

        return;
    }

    if ($data['id']) {
        $user = $this->app->container->auth->findById($data['id']);
        $user = $this->app->container->auth->validForUpdate($data) ? $this->app->container->auth->update($user, $data) : false;
    } else {
        $user = $this->app->container->auth->validForCreation($data) ? $this->app->container->auth->create($data) : false;
    }
});

//delete user
$app->delete('/admin/user/id', function($id) use($app) {
    if (!$loggedUser->hasAccess('user.*')) {
        echo "You don't have the permission to access this page.";

        return;
    }
    $user = $this->app->container->auth->findById($id);
    $user->delete($user);
});


$app->get('/admin/poll(/:id)', function ($id = null) use ($app) {
    if (!$loggedUser->hasAccess('poll.*')) {
        echo "You don't have the permission to access this page.";
        return;
    }

    $pollData = urukalo\CH\Poll::where('active', 1)->with('answers')->with('user_polls');

    if (!is_null($id)) {
        //is there no user vote on this poll?
        if ($pollData->has('user_polls', '=', 0)->find((int) $id)) {
            //no votes = can edit
            $app->twig->display('poll-edit.html.twig', array(
                "poll" => $pollData,
            ));
        } else {
            echo "editing isnt alowed";
        }
    } else {
        //show all polls
        $app->twig->display('polls.html.twig', array("poll" => $pollData->get()));
    }
});

//save edited poll or create new one
$app->post('/admin/poll(/:id)', function ($id = null) use ($app) {
    if (!$loggedUser->hasAccess('poll.*')) {
        echo "You don't have the permission to access this page.";
        return;
    }
    $data = $app->request->post();

    if (isset($data['id']))
        $poll = urukalo\CH\Poll::where('active', 1)->with('answers')->find((int) $id);
    else
        $poll = new urukalo\CH\Poll();

    $poll->name = $data['name'];
    $poll->question = $data['question'];
    $poll->public = $data['public'] == 'on' ? 1 : 0;
    $poll->active = $data['active'] == 'on' ? 1 : 0;
    $poll->archived = $data['archived'] == 'on' ? 1 : 0;
    $poll->save();

    //save all answers too
    $answer = array();
    foreach ($data['answer'] as $answerData) {
        $answer[] = new urukalo\CH\Answers($answerData);
    }
    $poll->answers()->saveMany($answer);
});

//delete poll
$app->delete('/admin/poll(/:id)', function ($id = null) use ($app) {
    if (!$loggedUser->hasAccess('poll.delete')) {
        echo "You don't have the permission to access this page.";
        return;
    }

    if ($poll = urukalo\CH\Poll::find((int) $id)->has('user_polls', '=', 0)) {
        $poll->delete();
    } else {
        echo "no poll to delete";
    }
});
