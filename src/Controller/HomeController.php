<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Form\ActivityType;
use App\Form\LicensePlateType;
use App\Repository\LicensePlateRepository;
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
    public function iveBlockedSomebody(Request $request, LicensePlateRepository $licensePlateRepository): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $licensePlate = $licensePlateRepository->findOneBy(['user' => $this->getUser()])->getLicensePlate();
            $activity->setBlocker($licensePlate);
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('activity/iblock.html.twig', [
            'blockee' => $activity,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/who_blocked_me', name: 'whoblocked')]
    public function whoBlockedMe(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
