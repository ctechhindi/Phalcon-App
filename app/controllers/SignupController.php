<?php

class SignupController extends ControllerBase
{

    public function indexAction()
    {
    }

    public function registerAction()
    {
        $user = new Users();

        // Store and check for errors
        $success = $user->save(
            $this->request->getPost(),
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

