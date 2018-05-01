<?php
use Phalcon\Http\Request;

// use form
use App\Forms\RegisterForm;

class SignupController extends ControllerBase
{

    public function indexAction()
    {
        $this->view->form = new RegisterForm();
    }

    public function registerAction()
    {
        $request = new Request();
        $user = new Users();
        $form = new RegisterForm(); 
        $mail = new Mail();

        // check request
        if (!$this->request->isPost()) {
            return $this->response->redirect('signup');
        }

        $form->bind($_POST, $user);
        // check form validation
        if (!$form->isValid()) {
            foreach ($form->getMessages() as $message) {
                $this->flashSession->error($message);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'index',
                ]);
                return;
            }
        }

        $user->setPassword($this->security->hash($_POST['password']));

        if (!$user->save()) {
            foreach ($user->getMessages() as $m) {
                $this->flashSession->error($m);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'index',
                ]);
                return;
            }
        }

        /**
         * Send Email
         */
        $params = [
            'name' => $this->request->getPost('name'),
            'link' => "http://localhost/_Phalcon/demo-app2/signup"
        ];

        $mail->send($this->request->getPost('email', ['trim', 'email']), 'signup', $params);

        $this->flashSession->success('Thanks for registering!');
        return $this->response->redirect('signup');

        // $success = $user->save(
        //     [
        //         "name" => $this->request->getPost('name', ['trim', 'string']),
        //         "email" => $this->request->getPost('email', ['trim', 'email']),
        //     ],
        //     [
        //         "name",
        //         "email",
        //     ]
        // );

        // if ($success) {
        //     $this->flashSession->success('Thanks for registering!');
        //     return $this->response->redirect('signup');
        // } else {
        //     echo "Sorry, the following problems were generated: ";
        //     foreach ($user->getMessages() as $message) {
        //         echo $message->getMessage(), "<br/>";
        //     }
        // }
        $this->view->disable();
    }
}

