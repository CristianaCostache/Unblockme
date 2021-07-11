<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Entity\User;
use App\Form\BlockeeType;
use App\Form\BlockerType;
use App\Form\UserType;
use App\Repository\LicensePlateRepository;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\UnicodeString;

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
        $entry = $licensePlateRepository->findOneBy(['user' => $this->getUser()]);
        if($entry)
        {
            $activity->setBlocker($entry->getLicensePlate());
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

            $activity->setBlockee((new UnicodeString($activity->getBlockee()))->camel()->upper());

            $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate'=>$activity->getBlockee()]);
            if($blockeeEntry)
            {
                $blockerEntry = $licensePlateRepository->findOneBy(['license_plate' => $activity->getBlocker()]);
                $this->addFlash(
                    'info',
                    'Your report was register and an email was send to the blocker!'
                );
                $mailer->sendReportEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(), 'blockee');
                //$mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockerEntry->getLicensePlate(), $blockeeEntry->getUser(), $blockeeEntry->getLicensePlate());
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
    public function whoBlockedMe(Request $request, LicensePlateRepository $licensePlateRepository, MailerService $mailer): Response
    {
        $activity = new Activity();
        $form = $this->createForm(BlockeeType::class, $activity);
        $entry = $licensePlateRepository->findOneBy(['user' => $this->getUser()]);
        if($entry)
        {
            $activity->setBlockee($entry->getLicensePlate());
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

            $activity->setBlocker((new UnicodeString($activity->getBlocker()))->camel()->upper());

            $blockerEntry = $licensePlateRepository->findOneBy(['license_plate'=>$activity->getBlocker()]);
            if($blockerEntry)
            {
                $this->addFlash(
                    'info',
                    'Your report was register and an email was send to the blockee!'
                );
                $blockeeEntry = $licensePlateRepository->findOneBy(['license_plate' => $activity->getBlockee()]);
                $mailer->sendReportEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(), $blockerEntry->getUser(), $blockerEntry->getLicensePlate(), 'blocker');
                //$mailer->sendBlockerEmail($blockeeEntry->getUser(), $blockeeEntry->getLicensePlate(),
                  //                          $blockerEntry->getUser(), $blockerEntry->getLicensePlate());
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

    #[Route('/change_password', name: 'change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->add('old_password', PasswordType::class, array('mapped' => false));
        $form->add('new_password', PasswordType::class, array('mapped' => false));
        $form->add('confirm_password', PasswordType::class, array('mapped' => false));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('old_password')->getData();
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if($user->getEmail() != $this->getUser()->getUserIdentifier())
            {
                $this->addFlash(
                    'warning',
                    "The email is not the same as the one of the already logged in user. Try again!"
                );
                return $this->redirectToRoute('change_password');
            }

            if(!$passwordHasher->isPasswordValid($this->getUser(), $oldPassword))
            {
                $this->addFlash(
                    'warning',
                    "The old password does not match. Try again!"
                );
                return $this->redirectToRoute('change_password');
            }

            if($newPassword != $confirmPassword)
            {
                $this->addFlash(
                    'warning',
                    "The new password does not match confirm password. Try again!"
                );
                return $this->redirectToRoute('change_password');
            }

            if((new UnicodeString($newPassword))->width() < 8)
            {
                $this->addFlash(
                    'warning',
                    "The password is too short. Please use a password that has at least 8 characters!"
                );
                return $this->redirectToRoute('change_password');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $this->getUser()->setPassword($passwordHasher->hashPassword($this->getUser(), $newPassword));
            $entityManager->persist($this->getUser());
            $entityManager->flush();

            $this->addFlash(
                'success',
                "The password has been successfully changed!"
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('password/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

}
