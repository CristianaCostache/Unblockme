<?php


namespace App\MessageHandler;

use App\Message\ReportMessage;
use App\Service\MailerService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReportMessageHandler implements MessageHandlerInterface
{

    private MailerService $mailerService;

    public function __construct(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }

    public function __invoke(ReportMessage $message)
    {
        $this->mailerService->sendReportEmail(
            $message->getReporterUser(),
            $message->getReporterLicensePlate(),
            $message->getReportedUser(),
            $message->getReportedLicensePlate(),
            $message->getTemplate()
        );
    }
}