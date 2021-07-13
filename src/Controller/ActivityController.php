<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Form\BlockeeType;
use App\Form\BlockerType;
use App\Repository\LicensePlateRepository;
use App\Service\LicensePlateService;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/activity')]
class ActivityController extends AbstractController
{
    #[Route('/ive_blocked_somebody', name: 'iblocked')]
    public function iveBlockedSomebody(Request $request, LicensePlateRepository $licensePlateRepository, MailerService $mailer,LicensePlateService $licensePlateService): Response
    {
        $activity = new Activity();
        $form = $this->createForm(BlockerType::class, $activity);
        $firstLicensePlate = $licensePlateService->getFirstLicensePlate($this->getUser());
        if($firstLicensePlate)
        {
            $activity->setBlocker($firstLicensePlate);
        }
        else
        {
            $this->addFlash(
                'info',
                'Your dont have any cars!'
            );
            return $this->redirectToRoute('home');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$activity->setBlockee((new UnicodeString($activity->getBlockee()))->camel()->upper());
            $activity->setBlockee($licensePlateService->normalizeLicensePlate($activity->getBlockee()));

            $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate'=>$activity->getBlockee()]);
            if($blockeeEntry)
            {
                if($blockeeEntry->getUser()) {
                    $blockerEntry = $licensePlateRepository->findOneBy(['license_plate' => $activity->getBlocker()]);
                    $this->addFlash(
                        'info',
                        'Your report was register and an email was send to the blocker!'
                    );
                    $mailer->sendReportEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(), 'blockee');
                    $activity->setStatus(1);
                    //$mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $blockeeEntry->getUser(), $blockeeEntry->getLicensePlate());
                }
                else{
                    $this->addFlash(
                        'warning',
                        'Your report was register but the blocker does not have an account! They will be contacted as soon as they are registered!'
                    );
                }
            }
            else
            {
                $this->addFlash(
                    'warning',
                    'Your report was register but the blocker does not have an account! They will be contacted as soon as they are registered!'
                );
                $licensePlate = new LicensePlate();
                $entityManager = $this->getDoctrine()->getManager();
                $licensePlate->setLicensePlate($activity->getBlockee());
                $entityManager->persist($licensePlate);
                $entityManager->flush();
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('activity/blocker.html.twig', [
            'blockee' => $activity,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/who_blocked_me', name: 'whoblocked')]
    public function whoBlockedMe(Request $request,LicensePlateRepository $licensePlateRepository, MailerService $mailer, LicensePlateService $licensePlateService): Response
    {
        $activity = new Activity();
        $form = $this->createForm(BlockeeType::class, $activity);
        $firstLicensePlate = $licensePlateService->getFirstLicensePlate($this->getUser());
        if($firstLicensePlate)
        {

            $activity->setBlockee($firstLicensePlate);
        }
        else
        {
            $this->addFlash(
                'info',
                'Your dont have any cars!'
            );
            return $this->redirectToRoute('home');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$activity->setBlocker((new UnicodeString($activity->getBlocker()))->camel()->upper());
            $activity->setBlocker($licensePlateService->normalizeLicensePlate($activity->getBlocker()));

            $blockerEntry = $licensePlateRepository->findOneBy(['license_plate'=>$activity->getBlocker()]);
            if($blockerEntry)
            {
                if($blockerEntry->getUser()) {
                    $this->addFlash(
                        'info',
                        'Your report was register and an email was send to the blockee!'
                    );
                    $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate' => $activity->getBlockee()]);
                    $mailer->sendReportEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(), $blockerEntry->getUser(), $blockerEntry->getLicensePlate(), 'blocker');
                    $activity->setStatus(1);
                    //$mailer->sendBlockerEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
                    //                          $blockerEntry->getUser(), $blockerEntry->getLicensePlate());
                }
                else{
                    $this->addFlash(
                        'warning',
                        'Your report was register but the blockee does not have an account! They will be contacted as soon as they are registered!'
                    );
                }
            }
            else
            {
                $this->addFlash(
                    'warning',
                    'Your report was register but the blockee does not have an account! They will be contacted as soon as they are registered!'
                );
                $licensePlate = new LicensePlate();
                $entityManager = $this->getDoctrine()->getManager();
                $licensePlate->setLicensePlate($activity->getBlocker());
                $entityManager->persist($licensePlate);
                $entityManager->flush();
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('activity/blockee.html.twig', [
            'blocker' => $activity,
            'form' => $form->createView(),
        ]);
    }
}
