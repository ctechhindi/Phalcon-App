<?php
namespace App\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Submit;

class RegisterForm extends Form
{
    public function initialize()
    {
        // form name field
        $name = new Text(
            'name',
            [
                "class" => "form-control",
                "placeholder" => "Enter Full Name"
            ]
        );

        // form email field
        $email = new Email(
            'email',
            [
                "class" => "form-control",
                "placeholder" => "Enter Email Address"
            ]
        );

        // form submit button
        $submit = new Submit(
            'submit',
            [
                "value" => "Register",
                "class" => "btn btn-primary",
            ]
        );

        $this->add($name);
        $this->add($email);
        $this->add($submit);
    }
}