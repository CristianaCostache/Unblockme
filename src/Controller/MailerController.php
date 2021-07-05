<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailerController extends AbstractController
{
    /**
     * @Route("/email", name="app_mailer")
     */
    public function sendEmail(MailerInterface $mailer, User $user, string $password): Response
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

        $mailer->send($email);

        return $this->redirectToRoute('app_login');
    }
}