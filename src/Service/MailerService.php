<?php

namespace App\Service;

use App\Entity\User;
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
    public function sendRegistrationEmail(User $user, string $password)
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

    public function sendBlockeeEmail(User $blocker, string $blockerLicensePlate,
                                     User $blockee, string $blockeeLicensePlate)
    {
        $email = (new TemplatedEmail())
            ->from('notification@unblockme.com')
            ->to($blockee->getUserIdentifier())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Somebody blocked you')
            ->htmlTemplate('mailer/blockeeNotification.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'blocker' => $blocker->getUserIdentifier(),
                'blockerLicensePlate' => $blockerLicensePlate,
                'blockeeLicensePlate' => $blockeeLicensePlate,
            ]);

        $this->mailer->send($email);
    }

    public function sendBlockerEmail(User $blockee, string $blockeeLicensePlate,
                                     User $blocker, string $blockerLicensePlate)
    {
        $email = (new TemplatedEmail())
            ->from('notification@unblockme.com')
            ->to($blocker->getUserIdentifier())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('You blocked somebody')
            ->htmlTemplate('mailer/blockerNotification.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'blockee' => $blockee->getUserIdentifier(),
                'blockeeLicensePlate' => $blockeeLicensePlate,
                'blockerLicensePlate' => $blockerLicensePlate,
            ]);

        $this->mailer->send($email);
    }
}