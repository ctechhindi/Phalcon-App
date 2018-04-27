<?php
use Phalcon\Http\Request;
// use Phalcon\Filter;

class SignupController extends ControllerBase
{

    public function indexAction()
    {
    }

    public function registerAction()
    {
        // Getting a request instance
        $request = new Request();
        // $filter = new Filter();

        // Check if request has made with POST
        if ($this->request->isPost()) {
            // Access POST data
            $name = $this->request->getPost('name', ['trim', 'string']);
            $email = $this->request->getPost('email', ['trim', 'email']);
        }

        // Returns 'Hello'
        // echo $filter->sanitize('<h1>Hello</h1>', ['striptags', 'trim']);

        $user = new Users();

        // Store and check for errors
        $success = $user->save(
            [
                "name" => $name,
                "email" => $email
            ],
            [
                "name",
                "email",
            ]
        );

        if ($success) {

            // Using session flash
            $this->flashSession->success('Thanks for registering!');

            // Make a full HTTP redirection
            return $this->response->redirect('signup');

        } else {
            echo "Sorry, the following problems were generated: ";

            $messages = $user->getMessages();

            foreach ($messages as $message) {
                echo $message->getMessage(), "<br/>";
            }
        }

        $this->view->disable();
    }
}

