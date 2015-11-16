<?php

//@todo -- replace 'echo' with slim flash-data (there is some bug now)
//list all users or get form to edit user
$app->get('/admin/user(/:id)', function ($id = null) use ($app) {
    $loggedUser = $app->container->auth->check();

    if (!$loggedUser->hasAccess('user.*')) {
        echo "You don't have the permission to access this page.";
        return;
    }
    $roles = $this->app->container->auth->getRoleRepository()->get();
    if ($id == 'add') {
        $app->twig->display('user-edit.html.twig', array(
            "roles" => $roles
        ));
    } elseif (!is_null($id) && $id != "add") {

        $user = $this->app->container->auth->findById((int) $id);

        $app->twig->display('user-edit.html.twig', array(
            "user-edit" => $user,
            "roles" => $roles
        ));
    } else {
        $users = $this->app->container->auth->getUserRepository()->get();

        $app->twig->display('users.html.twig', array(
            "users" => $users
        ));
    }
});

//save new or updated user
$app->post('/admin/user/:id', function($id) use($app) {
    $data = $app->request->post();
    $loggedUser = $app->container->auth->check();
    if (!$loggedUser->hasAccess('user.*')) {
        echo "You don't have the permission to access this page.";

        return;
    }

    if ($id && $id != 'add') {
        if ($data['password'] == '')
            unset($data['password']);
        $user = $app->container->auth->findById($id);
        if ($app->container->auth->validForUpdate($user, $data)) {
            $app->container->auth->update($user, $data);

            $role = $app->container->auth->findRoleById($data['role']);
            if (!$this->app->container->auth->inRole($role->slug))
                $role->users()->attach($user);
        }
    } else {


        $user = $app->container->auth->validForCreation($data) ? $app->container->auth->create($data) : false;
    }

    $app->redirect('/admin/user');
});

//delete user
$app->delete('/admin/user/id', function($id) use($app) {
    $loggedUser = $app->container->auth->check();
    if (!$loggedUser->hasAccess('user.*')) {
        echo "You don't have the permission to access this page.";

        return;
    }
    $user = $this->app->container->auth->findById($id);
    $user->delete($user);
});


$app->get('/admin/poll(/:id)', function ($id = null) use ($app) {
    $loggedUser = $app->container->auth->check();
    if (!$loggedUser->hasAccess('poll.*')) {
        echo "You don't have the permission to access this page.";
        return;
    }

    $pollData = urukalo\CH\Poll::where('active', 1)->with('answers')->with('user_polls');

    if (!is_null($id) && $id != "add") {
        //is there no user vote on this poll?
        if ($pollData->has('user_polls', '=', 0)->find((int) $id)) {

            //no votes = can edit
            $app->twig->display('poll-edit.html.twig', array(
                "poll" => $pollData->find((int) $id),
            ));
        } else {
            echo "editing isnt alowed";
        }
    } else {
        if ($id == 'add')
            $app->twig->display('poll-edit.html.twig');
        else
        //show all polls
            $app->twig->display('polls-admin.html.twig', array("poll" => $pollData->get()));
    }
});

//save edited poll or create new one
$app->post('/admin/poll(/:id)', function ($id = null) use ($app) {
    $loggedUser = $app->container->auth->check();
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
    $poll->public = isset($data['public']) && $data['public'] == 'on' ? 1 : 0;
    $poll->active = isset($data['active']) && $data['active'] == 'on' ? 1 : 0;
    $poll->archived = isset($data['archived']) && $data['archived'] == 'on' ? 1 : 0;
    $poll->save();

    //save all answers too
    $answer = array();
    foreach ($data['answer'] as $answerData) {
        $answer[] = new urukalo\CH\Answers($answerData);
    }
    $poll->answers()->saveMany($answer);

    $app->redirect('/admin/poll');
});

//delete poll
$app->delete('/admin/poll(/:id)', function ($id = null) use ($app) {
    $loggedUser = $app->container->auth->check();
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
