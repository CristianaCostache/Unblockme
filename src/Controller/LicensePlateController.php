<?php

namespace App\Controller;

use App\Entity\LicensePlate;
use App\Form\LicensePlateType;
use App\Repository\LicensePlateRepository;
use App\Service\MailerService;
use App\Service\ActivityService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\UnicodeString;

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
     * @throws NonUniqueResultException|\Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/new', name: 'license_plate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ActivityService $activity, MailerService $mailer, LicensePlateRepository $licensePlateRepository): Response
    {
        $licensePlate = new LicensePlate();
        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $validLicensePlate = (new UnicodeString($licensePlate->getLicensePlate()))->camel()->upper();
            $licensePlate->setLicensePlate($validLicensePlate);

            $entry = $licensePlateRepository->findOneBy(['license_plate' => $licensePlate->getLicensePlate()]);
            $entityManager = $this->getDoctrine()->getManager();
            //$messageReport = '';
            if($entry and !$entry->getUser())
            {
                $entry->setUser($this->getUser());
                $entityManager->persist($entry);
                $entityManager->flush();

                $blocker = $activity->whoBlockedMe($licensePlate->getLicensePlate());
                if($blocker)
                {
                    $blockerEntry = $licensePlateRepository->findOneBy(['license_plate' => $blocker]);
                    $messageReport = $blockerEntry->getLicensePlate() . " has blocked you";
//                    $this->addFlash(
//                        'notice',
//                        $messageReport
//                    );
                    $message = 'The car with the license plate ' . $licensePlate->getLicensePlate() . ' has been added! ' . $messageReport;
                    $this->addFlash(
                        'warning',
                        $message
                    );
                    $mailer->sendReportEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(),
                        $entry->getUser(), $entry->getLicensePlate(), 'blockee');
                    //$mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $entry->getUser(), $entry->getLicensePlate());
                }

                $blockee = $activity->iveBlockedSomebody($licensePlate->getLicensePlate());
                if($blockee)
                {
                    $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate' => $blockee]);
                    $messageReport = "You blocked " . $blockeeEntry->getLicensePlate();
//                    $this->addFlash(
//                        'notice',
//                        $messageReport
//                    );
                    $message = 'The car with the license plate ' . $licensePlate->getLicensePlate() . ' has been added! ' . $messageReport;
                    $this->addFlash(
                        'danger',
                        $message
                    );
                    $mailer->sendReportEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
                        $entry->getUser(), $entry->getLicensePlate(), 'blocker');
                    //$mailer->sendBlockerEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
                      //  $entry->getUser(), $entry->getLicensePlate());
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
    public function edit(Request $request, LicensePlate $licensePlate): Response
    {
        $oldLicensePlate = $licensePlate->getLicensePlate();
        $message = 'Car ' . $licensePlate->getLicensePlate() . ' has been change to ';
        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validLicensePlate = (new UnicodeString($licensePlate->getLicensePlate()))->camel()->upper();
            if($oldLicensePlate != $validLicensePlate)
            {
                $licensePlate->setLicensePlate($validLicensePlate);
                $message = $message . $licensePlate->getLicensePlate() . '!';
                $this->addFlash(
                    'success',
                    $message
                );

                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('license_plate_index');
            }
            else
            {
                $message = 'You insert the same license plate!';
                $this->addFlash(
                    'warning',
                    $message
                );
                return $this->redirectToRoute('license_plate_index');
            }
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

            $message = 'The license plate ' . $licensePlate->getLicensePlate() . ' was deleted!';
            $this->addFlash(
                'success',
                $message
            );
        }

        return $this->redirectToRoute('license_plate_index');
    }
}
