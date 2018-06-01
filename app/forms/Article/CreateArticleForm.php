<?php
namespace App\Forms\Article;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Submit;
// Validation
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Email;

class CreateArticleForm extends Form
{
    public function initialize($entity = null, $options = [])
    {
        if (isset($options["edit"])) {
            $id = new Hidden('eid', [
                "required" => true,
            ]);

            $this->add($id);
        }

        /**
         * Atricle Title
         */
        $title = new Text('title', [
            "class" => "form-control",
            // "required" => true,
            "placeholder" => "Article Title"
        ]);

        // form title field validation
        $title->addValidators([
            new PresenceOf(['message' => 'Article Title is required']),
        ]);

        /**
         * Article Description
         */
        $description = new textArea('description', [
            "class" => "form-control",
            // "required" => true,
            "placeholder" => "Article Description",
            "rows" => "5"
        ]);
        
        // description field validation
        $description->addValidators([
            new PresenceOf(['message' => 'Article Description is required']),
            new StringLength(['min' => 50, 'message' => 'Description is too short. Minimum 50 characters.']),
        ]);

        /**
         * Save Button
         */
        $save = new Submit('save', [
            "name" => "save",
            "value" => "Save Draft",
            "class" => "btn btn-white",
        ]);

        /**
         * Save Button
         */
        $publish = new Submit('publish', [
            "name" => "publish",
            "value" => "Publish",
            "class" => "btn btn-primary",
        ]);

        $this->add($title);
        $this->add($description);
        $this->add($save);
        $this->add($publish);
    }
}