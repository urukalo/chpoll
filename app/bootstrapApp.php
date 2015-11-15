<?php

namespace urukalo\CH;

/**
 * Description of bootstrapApp
 *
 * @author milan
 */
class bootstrapApp {

    private $app;

    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    /**
     * add twig template engine
     */
    public function connectToTwig() {
        $this->app->container->twigLoader = new \Twig_Loader_Filesystem(__DIR__ . '/views');
        $this->app->container->twig = new \Twig_Environment($this->app->container->twigLoader, array('cache' => false));
    }

    /**
     * connect to database -- using Eloquent
     * @param type $capsule
     * @return type
     */
    public function connectToDatabase($capsule) {
        // Register Eloquent configuration

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'ch',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]);
        $capsule->bootEloquent();

        return $capsule;
    }

    /**
     * add auth to container
     * @param type $auth
     */
    public function connectAuth($auth) {
        $this->app->container->auth = $auth;
        $this->addGlobalUser();
    }

    /**
     * just to separate routes in logical parts
     */
    public function loadRoutes() {
        $app = $this->app;

        //fast routes split 
        include_once 'routes/site.php';
        include_once 'routes/user.php';
        include_once 'routes/admin.php';
        include_once 'routes/poll.php';

        //$this->app = $app;
    }

    /**
     * run Forest, run.. 
     */
    public function runApp() {

        $this->app->run();
    }

    private function addGlobalUser() {
        $user = $this->app->container->auth->check();
        if ($user)
            $this->app->container->twig->addGlobal('user', $user);
    }

    /**
     * only to populate table - one time use!!
     */
    public function initRules() {
        $this->app->container->auth->getRoleRepository()->createModel()->create(array(
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => array(
                'user.create' => true,
                'user.update' => true,
                'user.delete' => true
            ),
        ));

        $this->app->container->auth->getRoleRepository()->createModel()->create(array(
            'name' => 'User',
            'slug' => 'user',
            'permissions' => array(
                'user.update' => true
            ),
        ));
    }

}
