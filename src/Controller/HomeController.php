<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Form\BlockeeType;
use App\Form\BlockerType;
use App\Repository\LicensePlateRepository;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/ive_blocked_somebody', name: 'iblocked')]
    public function iveBlockedSomebody(Request $request, LicensePlateRepository $licensePlateRepository, MailerService $mailer): Response
    {
        $activity = new Activity();
        $form = $this->createForm(BlockerType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate'=>$activity->getBlockee()]);
            if($blockeeEntry)
            {
                $blockerEntry = $licensePlateRepository->findOneBy(['license_plate' => $activity->getBlocker()]);
                $mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $blockeeEntry->getUser(), $blockeeEntry->getLicensePlate());
            }
            else
            {
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
    public function whoBlockedMe(Request $request, LicensePlateRepository $licensePlateRepository, MailerService $mailer): Response
    {
        $activity = new Activity();
        $form = $this->createForm(BlockeeType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blockerEntry = $licensePlateRepository->findOneBy(['license_plate'=>$activity->getBlocker()]);
            if($blockerEntry)
            {
                $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate' => $activity->getBlockee()]);
                $mailer->sendBlockerEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
                                            $blockerEntry->getUser(), $blockerEntry->getLicensePlate());
            }
            else
            {
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
