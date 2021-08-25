<?php


namespace App\MessageHandler;

use App\Message\RegisterMessage;
use App\Service\MailerService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RegisterMessageHandler implements MessageHandlerInterface
{

    private MailerService $mailerService;

    public function __construct(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }

    public function __invoke(RegisterMessage $message)
    {
        $this->mailerService->sendRegistrationEmail(
            $message->getUser(),
            $message->getPassword()
        );
    }
}