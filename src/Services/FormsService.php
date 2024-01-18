<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class FormsService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(string $from, string $to, string $subject, string $template, array $context): void {
        // Validation des arguments
        if (empty($from) || empty($to) || empty($subject) || empty($template) || empty($context)) {
            throw new \InvalidArgumentException('Tous les arguments doivent être fournis.');
        }

        $email = (new TemplatedEmail())
            ->from($from)
            ->to(new Address($to))
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function validateRegister(string $userMail, string $userName, string $token){
        $this->send(
            'no-reply@lasallecrossfit.fr',
            $userMail,
            "Création de votre compte sur le site",
            'register',
            [
                'user' => $userName,
                'token' => $token
            ]
        );
    }

    public function confirmRegister(string $userMail, string $userName, string $token){
        $this->send(
            'no-reply@lasallecrossfit.fr',
            $userMail,
            "Création de votre compte sur le site",
            'confirm',
            [
                'user' => $userName,
                'token' => $token
            ]
        );
    }
}
