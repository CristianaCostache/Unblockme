<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailerService
{
    private MailerInterface $mailer;
    /**
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/email", name="app_mailer")
     * @throws TransportExceptionInterface
     */
    public function sendEmail(User $user, string $password)
    {
        $email = (new TemplatedEmail())
            ->from('register@unblockme.com')
            ->to($user->getUserIdentifier())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Thanks for signing up!')
            ->htmlTemplate('mailer/index.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'username' => $user->getUserIdentifier(),
                    'password' => $password,
                ]);

        $this->mailer->send($email);
    }
}