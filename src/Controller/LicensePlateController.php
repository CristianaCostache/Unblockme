<?php

namespace App\Controller;

use App\Entity\LicensePlate;
use App\Form\LicensePlateType;
use App\Message\ReportMessage;
use App\Repository\LicensePlateRepository;
use App\Service\LicensePlateService;
use App\Service\ActivityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/new', name: 'license_plate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ActivityService $activity, LicensePlateRepository $licensePlateRepository, LicensePlateService $licensePlateService, MessageBusInterface $messageBus): Response
    {
        $licensePlate = new LicensePlate();
        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $licensePlate->setLicensePlate($licensePlateService->normalizeLicensePlate($licensePlate->getLicensePlate()));

//            $messageBus->dispatch(new ReportMessage($this->getUser(), $licensePlate,
//                $this->getUser(), $licensePlate, 'blockee'));

            $entry = $licensePlateRepository->findOneBy(['license_plate' => $licensePlate->getLicensePlate()]);
            if($entry && $entry->getUser() == $this->getUser())
            {
                $this->addFlash(
                    'warning',
                    'You already have this car!'
                );
                return $this->redirectToRoute('license_plate_index');
            }

            $entityManager = $this->getDoctrine()->getManager();
            //$messageReport = '';
            if($entry and !$entry->getUser())
            {
                $entry->setUser($this->getUser());
                $entityManager->persist($entry);
                $entityManager->flush();

                $blocker = $activity->whoBlockedMe($licensePlate->getLicensePlate());
                //dd($blocker);
                if($blocker)
                {
                    foreach ($blocker as &$it)
                    {
                        $blockerEntry = $licensePlateRepository->findOneBy(['license_plate' => $it->getBlocker()]);
                        $messageReport = $blockerEntry->getLicensePlate() . " has blocked you";

                        $message = 'The car with the license plate ' . $licensePlate->getLicensePlate() . ' has been added! ' . $messageReport;
                        $this->addFlash(
                            'warning',
                            $message
                        );
                        if($blockerEntry->getUser())
                        {

//                            $messageBus->dispatch(new ReportMessage($blockerEntry->getUser(), $blockerEntry->getLicensePlate(),
//                                $entry->getUser(), $entry->getLicensePlate(), 'blockee'));
//                            $mailer->sendReportEmail($blockerEntry->getUser(), $blockerEntry->getUser(),
//                                $entry->getUser(), $entry->getLicensePlate(), 'blockee');

                            $messageBus->dispatch(new ReportMessage($blockerEntry->getUser(), $blockerEntry->getLicensePlate(),
                                $entry->getUser(), $entry->getLicensePlate(), 'blockee'));

                            $it->setStatus(1);
                            $entityManager->persist($it);
                            $entityManager->flush();
                        }
                    }


                    //$mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $entry->getUser(), $entry->getLicensePlate());
                }

                $blockee = $activity->iveBlockedSomebody($licensePlate->getLicensePlate());
                if($blockee)
                {
                    foreach ($blockee as &$it) {
                        $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate' => $it->getBlockee()]);
                        $messageReport = "You blocked " . $blockeeEntry->getLicensePlate();
                        $message = 'The car with the license plate ' . $licensePlate->getLicensePlate() . ' has been added! ' . $messageReport;
                        $this->addFlash(
                            'danger',
                            $message
                        );
                        if($blockeeEntry->getUser())
                        {
//                            $mailer->sendReportEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
//                                $entry->getUser(), $entry->getLicensePlate(), 'blocker');
                            $messageBus->dispatch(new ReportMessage($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
                                $entry->getUser(), $entry->getLicensePlate(), 'blocker'));
                            $it->setStatus(1);
                            $entityManager->persist($it);
                            $entityManager->flush();
                        }
                    }

                }

                return $this->redirectToRoute('license_plate_index');
            }

            $licensePlate->setUser($this->getUser());
            $entityManager->persist($licensePlate);
            $entityManager->flush();

            $message = 'The car with the license plate ' . $licensePlate->getLicensePlate() . ' has been added! ';
            $this->addFlash(
                'success',
                $message
            );

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
    public function edit(Request $request, LicensePlate $licensePlate, LicensePlateService $licensePlateService, ActivityService $activityService, LicensePlateRepository $licensePlateRepository): Response
    {
        $oldLicensePlate = $licensePlate->getLicensePlate();
        $message = 'Car ' . $licensePlate->getLicensePlate() . ' has been change to ';
        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newLicensePlate = $licensePlateService->normalizeLicensePlate($licensePlate);

            if($oldLicensePlate == $newLicensePlate)
            {
                $message = 'You insert the same license plate!';
                $this->addFlash(
                    'warning',
                    $message
                );
                return $this->redirectToRoute('license_plate_index');
            }

            $entry = $licensePlateRepository->findOneBy(['license_plate' => $newLicensePlate, 'user' => $this->getUser()]);
            if($entry)
            {
                $this->addFlash(
                    'warning',
                    'You already have this car!'
                );
                return $this->redirectToRoute('license_plate_index');
            }

            $blocker = $activityService->iveBlockedSomebody($oldLicensePlate);
            $blockee = $activityService->whoBlockedMe($oldLicensePlate);

            if($blockee != null || $blocker != null)
            {
                $this->addFlash(
                    'warning',
                    'You cant change your license plate because it is part of an activity report!'
                );
                return $this->redirectToRoute('license_plate_index');
            }

            $diff = $licensePlateService->getDurationBetweenUpdates($licensePlate);
            //dd($diff->d);

            if($diff < 86400)
            {
                $this->addFlash(
                    'warning',
                    'You can only change your license plate after one day!'
                );
                return $this->redirectToRoute('license_plate_index');
            }

            $licensePlate->setLicensePlate($newLicensePlate);
            $message = $message . $licensePlate->getLicensePlate() . '!';
            $this->addFlash(
                'success',
                $message
            );

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('license_plate_index');
        }

        return $this->render('license_plate/edit.html.twig', [
            'license_plate' => $licensePlate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'license_plate_delete', methods: ['POST'])]
    public function delete(Request $request, LicensePlate $licensePlate, ActivityService $activityService): Response
    {
        $oldLicensePlate = $licensePlate->getLicensePlate();
        $blocker = $activityService->iveBlockedSomebody($oldLicensePlate);
        $blockee = $activityService->whoBlockedMe($oldLicensePlate);

        if($blockee != null || $blocker != null)
        {
            $this->addFlash(
                'warning',
                'You cannot delete your license plate because it is part of an activity report!'
            );
            return $this->redirectToRoute('license_plate_index');
        }

        if ($this->isCsrfTokenValid('delete'.$licensePlate->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($licensePlate);
            $entityManager->flush();

            $message = 'The license plate ' . $licensePlate->getLicensePlate() . ' was deleted!';
            $this->addFlash(
                'success',
                $message
            );
        }

        return $this->redirectToRoute('license_plate_index');
    }
}
