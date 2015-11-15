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

    public function connectToViewEngine() {
        $this->app->container->twigLoader = new \Twig_Loader_Filesystem(__DIR__ . '/views');
        $this->app->container->twig = new \Twig_Environment($this->app->container->twigLoader, array(
            'cache' => false, //__DIR__.'/../cache',
        ));
       
    }

    public function connectToDatabase() {
        // Register Eloquent configuration
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'ch',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]);
        $capsule->bootEloquent();

        return $capsule;
    }
    
    public function loadRoutes(){
        $app = $this->app;
        
        include_once 'routes.php';
        
        //$this->app = $app;
    }
    
    public function runApp() {
        $this->app->run();
        
    }

}
