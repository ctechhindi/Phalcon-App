<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Crypt;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();


    // Register the flash service with custom CSS classes
    $di->set(
        'flash',
        function () {
            $flash = new FlashDirect(
                [
                    'error'   => 'alert alert-danger',
                    'success' => 'alert alert-success',
                    'notice'  => 'alert alert-info',
                    'warning' => 'alert alert-warning',
                ]
            );

            return $flash;
        }
    );

    // Register the flash service with custom CSS classes
    $di->set(
        'flashSession',
        function () {
            $flash = new FlashSession(
                [
                    'error'   => 'alert alert-danger',
                    'success' => 'alert alert-success',
                    'notice'  => 'alert alert-info',
                    'warning' => 'alert alert-warning',
                ]
            );

            return $flash;
        }
    );


    // Start the session the first time when some component request the session service
    $di->setShared(
        'session',
        function () {
            $session = new Session();

            $session->start();

            return $session;
        }
    );

    # https://stackoverflow.com/questions/24446258/phalcon-not-found-page-error-handler
    $di->set('dispatcher', function() {

        $eventsManager = new \Phalcon\Events\Manager();
    
        $eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception) {
    
            //Handle 404 exceptions
            if ($exception instanceof \Phalcon\Mvc\Dispatcher\Exception) {
                $dispatcher->forward(array(
                    'controller' => 'index',
                    'action' => 'show404'
                ));
                return false;
            }
    
            //Handle other exceptions
            $dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'show503'
            ));
    
            return false;
        });
    
        $dispatcher = new \Phalcon\Mvc\Dispatcher();
    
        //Bind the EventsManager to the dispatcher
        $dispatcher->setEventsManager($eventsManager);
    
        return $dispatcher;
    
    }, true);

    /**
     * Encryption/Decryption
     * ----------------------
     * https://docs.phalconphp.com/en/3.3/crypt
     */
    $di->set(
        'crypt',
        function () {
            $crypt = new Crypt();
    
            // Set a global encryption key
            $crypt->setKey(
                "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3"
            );
    
            return $crypt;
        },
        true
    );

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo str_replace(["\n","\r","\t"], '', $application->handle()->getContent());

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
