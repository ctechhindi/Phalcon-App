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
            
        // Check User Active
        if ($user->active != 1) {
            $this->flashSession->error("User Deactivate");
            return $this->response->redirect('user/login');
        }
        
        # Doc :: https://docs.phalconphp.com/en/3.3/security
        if ($user) {
            if ($this->security->checkHash($password, $user->password))
            {
                # https://docs.phalconphp.com/en/3.3/session#start

                // Set a session
                $this->session->set('AUTH_ID', $user->id);
                $this->session->set('AUTH_NAME', $user->name);
                $this->session->set('AUTH_EMAIL', $user->email);
                $this->session->set('AUTH_CREATED', $user->created);
                $this->session->set('AUTH_UPDATED', $user->updated);
                $this->session->set('IS_LOGIN', 1);

                // $this->flashSession->success("Login Success");
                return $this->response->redirect('user/profile');
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

    /**
     * Register Page View
     */
    public function registerAction()
    {
        $this->tag->setTitle('Phalcon :: Register');
        $this->view->form = new RegisterForm();
    }

    /**
     * Register Action
     * @method: POST
     * @param: name
     * @param: email
     * @param: password
     */
    public function registerSubmitAction()
    {
        $form = new RegisterForm(); 
        $mail = new Mail();

        // check request
        if (!$this->request->isPost()) {
            return $this->response->redirect('user/register');
        }

        $form->bind($_POST, $this->usersModel);
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
        $this->usersModel->setPassword($this->security->hash($_POST['password']));

        $this->usersModel->setActive(1);
        $this->usersModel->setCreated(time());
        $this->usersModel->setUpdated(time());
        
        # Doc :: https://docs.phalconphp.com/en/3.3/db-models#create-update-records
        if (!$this->usersModel->save()) {
            foreach ($this->usersModel->getMessages() as $m) {
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


    /**
     * User Profile
     */
    public function profileAction()
    {
        $this->authorized();
    }

    /**
     * User Logout
     */
    public function logoutAction()
    {
        # https://docs.phalconphp.com/en/3.3/session#remove-destroy

        // Destroy the whole session
        $this->session->destroy();
        return $this->response->redirect('user/login');
    }
}

