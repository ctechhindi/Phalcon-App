<?php

// namespace App\Mail;

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View;

require_once BASE_PATH .'/vendor/autoload.php';

use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;
use Swift_SmtpTransport as SwiftSmtpTransport;

class Mail extends Component
{
    protected $transport;

    private function getTemplate($keys, $params)
    {
        $render = $this->view->getRender(
            'templates',
            $keys,
            $params,
            function ($view) {
                $view->setRenderLevel(View::LEVEL_LAYOUT);
            }
        );

        if (!empty($render)) {
            return $render;
        }

        // When use template for cli
        return $this->view->getContent();
    }

    /**
     * Sent Mail
     * 
     * @param $to : email to send
     * @param $temlateKey
     * @param array $params
     * 
     * @return int
     */
    public function send($to, $temlateKey, $params = [])
    {
        $body = $this->getTemplate($temlateKey, $params);
        if (!$body) {
            throw new \Exception('You need to create templates email.');
        }

        if (empty($params['subject'])) {
            $subject = "Phalcon Demo App";
        } else {
            $subject = $params['subject'];
        }

        $mail = $this->config->mail;
        // Create the message
        $message = new SwiftMessage($subject);
        $message
            ->setTo([$to])
            ->setFrom([$mail->fromEmail => $mail->fromName])
            ->setBody($body, 'text/html');

        if (!$this->transport) {
            $transport = new SwiftSmtpTransport($mail->smtp->server, $mail->smtp->port);
            $transport->setUsername($mail->smtp->username);
            $transport->setPassword($mail->smtp->password);
            $this->transport = $transport;
        }

        $mailer = new SwiftMailer($this->transport);
        return $mailer->send($message);
    }
}