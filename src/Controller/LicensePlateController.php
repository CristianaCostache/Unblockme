<?php

namespace App\Controller;

use App\Entity\LicensePlate;
use App\Form\LicensePlateType;
use App\Repository\LicensePlateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/license/plate')]
class LicensePlateController extends AbstractController
{
    #[Route('/', name: 'license_plate_index', methods: ['GET'])]
    public function index(LicensePlateRepository $licensePlateRepository): Response
    {
        return $this->render('license_plate/index.html.twig', [
            'license_plates' => $licensePlateRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

//    #[Route('/cars', name: 'user_license_plate_index', methods: ['GET'])]
//    public function user_cars(LicensePlateRepository $licensePlateRepository): Response
//    {
//        //dd($this->getUser());
//        return $this->render('license_plate/index.html.twig', [
//            'license_plates' => $licensePlateRepository->findBy(['user' => $this->getUser()]),
//        ]);
//    }

    #[Route('/new', name: 'license_plate_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $licensePlate = new LicensePlate();
        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $licensePlate->setUser($this->getUser());
            $entityManager->persist($licensePlate);
            $entityManager->flush();

            return $this->redirectToRoute('license_plate_index');
        }

        return $this->render('license_plate/new.html.twig', [
            'license_plate' => $licensePlate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'license_plate_show', methods: ['GET'])]
    public function show(LicensePlate $licensePlate): Response
    {
        return $this->render('license_plate/show.html.twig', [
            'license_plate' => $licensePlate,
        ]);
    }

    #[Route('/{id}/edit', name: 'license_plate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LicensePlate $licensePlate): Response
    {
        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('license_plate_index');
        }

        return $this->render('license_plate/edit.html.twig', [
            'license_plate' => $licensePlate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'license_plate_delete', methods: ['POST'])]
    public function delete(Request $request, LicensePlate $licensePlate): Response
    {
        if ($this->isCsrfTokenValid('delete'.$licensePlate->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($licensePlate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('license_plate_index');
    }
}
