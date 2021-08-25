<?php


namespace App\Message;

use App\Entity\User;

class ReportMessage
{
    private User $reporterUser;
    private string $reporterLicensePlate;
    private User $reportedUser;
    private string $reportedLicensePlate;
    private string $template;

    public function __construct(User $reporterUser, string $reporterLicensePlate,
                                User $reportedUser, string $reportedLicensePlate, string $template)
    {
        $this->reporterUser = $reporterUser;
        $this->reporterLicensePlate = $reporterLicensePlate;
        $this->reportedUser = $reportedUser;
        $this->reportedLicensePlate = $reportedLicensePlate;
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getReporterUser()
    {
        return $this->reporterUser;
    }

    /**
     * @return mixed
     */
    public function getReporterLicensePlate()
    {
        return $this->reporterLicensePlate;
    }

    /**
     * @return mixed
     */
    public function getReportedUser()
    {
        return $this->reportedUser;
    }

    /**
     * @return mixed
     */
    public function getReportedLicensePlate()
    {
        return $this->reportedLicensePlate;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

}
