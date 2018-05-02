<?php
use Phalcon\Http\Request;

// use form
use App\Forms\RegisterForm;
use App\Forms\LoginForm;

class UserController extends ControllerBase
{
    public $loginForm;
    public $usersModel;

    public function onConstruct()
    {
    }

    public function initialize()
    {
        $this->loginForm = new LoginForm();
        $this->usersModel = new Users();
    }

    public function indexAction() {}
    
    /**
     * Login Page View
     */
    public function loginAction()
    {
        /**
         * Changing dynamically the Document Title
         * ------------------------------------------
         * @setTitle()
         * @prependTitle()
         */
        $this->tag->setTitle('Phalcon :: Login');
        // Login Form
        $this->view->form = new LoginForm();
    }

    /**
     * Login Action
     * @method: POST
     * @param: email
     * @param: password
     */
    public function loginSubmitAction()
    {
        // check request
        if (!$this->request->isPost()) {
            return $this->response->redirect('user/login');
        }

        # https://docs.phalconphp.com/en/3.3/security#csrf

        // Validate CSRF token
        if (!$this->security->checkToken()) {
            $this->flashSession->error("Invalid Token");
            return $this->response->redirect('user/login');
        }

        $this->loginForm->bind($_POST, $this->usersModel);
        // check form validation
        if (!$this->loginForm->isValid()) {
            foreach ($this->loginForm->getMessages() as $message) {
                $this->flashSession->error($message);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'login',
                ]);
                return;
            }
        }
        
        // login with database
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        /**
         * Users::findFirst();
         * $this->usersModel->findFirst();
         */
        $user = Users::findFirst([ 
            'email = :email:',
            'bind' => [
               'email' => $email,
            ]
        ]);
        
        # Doc :: https://docs.phalconphp.com/en/3.3/security
        if ($user) {
            if ($this->security->checkHash($password, $user->password)) {
                // The password is valid
                $this->flashSession->success("Login Success");
                return $this->response->redirect('user/login');
            }
        } else {
            // To protect against timing attacks. Regardless of whether a user
            // exists or not, the script will take roughly the same amount as
            // it will always be computing a hash.
            $this->security->hash(rand());
        }

        // The validation has failed
        $this->flashSession->error("Invalid login");
        return $this->response->redirect('user/login');
    }

    public function registerAction()
    {
        $this->tag->setTitle('Phalcon :: Register');
        $this->view->form = new RegisterForm();
    }

    public function registerSubmitAction()
    {
        $user = new Users();
        $form = new RegisterForm(); 
        $mail = new Mail();

        // check request
        if (!$this->request->isPost()) {
            return $this->response->redirect('user/register');
        }

        $form->bind($_POST, $user);
        // check form validation
        if (!$form->isValid()) {
            foreach ($form->getMessages() as $message) {
                $this->flashSession->error($message);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'register',
                ]);
                return;
            }
        }

        # Doc :: https://docs.phalconphp.com/en/3.3/security
        $user->setPassword($this->security->hash($_POST['password']));

        $user->setActive(1);
        $user->setCreated(time());
        $user->setUpdated(time());
        
        # Doc :: https://docs.phalconphp.com/en/3.3/db-models#create-update-records
        if (!$user->save()) {
            foreach ($user->getMessages() as $m) {
                $this->flashSession->error($m);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'register',
                ]);
                return;
            }
        }

        /**
         * Send Email
         */
        // $params = [
        //     'name' => $this->request->getPost('name'),
        //     'link' => "http://localhost/_Phalcon/demo-app2/signup"
        // ];
        // $mail->send($this->request->getPost('email', ['trim', 'email']), 'signup', $params);

        $this->flashSession->success('Thanks for registering!');
        return $this->response->redirect('user/register');

        $this->view->disable();
    }
}

