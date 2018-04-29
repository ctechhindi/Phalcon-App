<?php
namespace App\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
// Validation
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;

class RegisterForm extends Form
{
    public function initialize()
    {
        /**
         * Name
         */
        $name = new Text('name', [
            "class" => "form-control",
            // "required" => true,
            "placeholder" => "Enter Full Name"
        ]);

        // form name field validation
        $name->addValidator(
            new PresenceOf(['message' => 'The name is required'])
        );

        /**
         * Email Address
         */
        $email = new Text('email', [
            "class" => "form-control",
            // "required" => true,
            "placeholder" => "Enter Email Address"
        ]);

        // form email field validation
        $email->addValidators([
            new PresenceOf(['message' => 'The email is required']),
            new Email(['message' => 'The e-mail is not valid']),
        ]);

        /**
         * New Password
         */
        $password = new Password('password', [
            "class" => "form-control",
            // "required" => true,
            "placeholder" => "Your Password"
        ]);

        $password->addValidators([
            new PresenceOf(['message' => 'Password is required']),
            new StringLength(['min' => 5, 'message' => 'Password is too short. Minimum 5 characters.']),
            new Confirmation(['with' => 'password_confirm', 'message' => 'Password doesn\'t match confirmation.']),
        ]);


        /**
         * Confirm Password
         */
        $passwordNewConfirm = new Password('password_confirm', [
            "class" => "form-control",
            // "required" => true,
            "placeholder" => "Confirm Password"
        ]);

        $passwordNewConfirm->addValidators([
            new PresenceOf(['message' => 'The confirmation password is required']),
        ]);


        /**
         * Submit Button
         */
        $submit = new Submit('submit', [
            "value" => "Register",
            "class" => "btn btn-primary",
        ]);

        $this->add($name);
        $this->add($email);
        $this->add($password);
        $this->add($passwordNewConfirm);
        $this->add($submit);
    }
}