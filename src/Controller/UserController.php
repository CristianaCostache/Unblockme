<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Repository\LicensePlateRepository;
use App\Service\ActivityService;
use App\Service\LicensePlateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/change_password', name: 'change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('new_password')->getData();

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

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, LicensePlateService $licensePlateService): Response
    {
        //
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            //dd($user);
            $licensePlateService->removeUser($user);
            //dd($user);
            $entityManager->remove($user);

            $session = $this->get('session');
            $session = new Session();
            $session->invalidate();

            $entityManager->flush();

            $message = 'The account was deleted!';
            $this->addFlash(
                'success',
                $message
            );
        }

        //dd($user);
        return $this->redirectToRoute('app_login');
    }

    #[Route('/export_data', name: 'export_data')]
    public function exportData(Request $request, LicensePlateRepository $licensePlateRepository, LicensePlateService $licensePlateService, ActivityService $activityService): Response
    {
        $encoders = [new XmlEncoder(), new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $csvContent = $serializer->serialize($this->getUser(), 'csv', [AbstractNormalizer::ATTRIBUTES => ['email', 'roles']]);
        $filesystem = new Filesystem();
        $filesystem->dumpFile('fileData.txt', $csvContent);
        $csvContent = $serializer->serialize($licensePlateService->getAllLicensePlates($this->getUser()), 'csv', [AbstractNormalizer::ATTRIBUTES => ['licensePlate']]);
        $filesystem->appendToFile('fileData.txt', $csvContent);
        $csvContent = $serializer->serialize($activityService->allMyBlockees($this->getUser(), $licensePlateService), 'csv', [AbstractNormalizer::ATTRIBUTES => ['blocker', 'blockee']]);
        $filesystem->appendToFile('fileData.txt', $csvContent);
        $csvContent = $serializer->serialize($activityService->allMyBlockers($this->getUser(), $licensePlateService), 'csv', [AbstractNormalizer::ATTRIBUTES => ['blockee', 'blocker']]);
        $filesystem->appendToFile('fileData.txt', $csvContent);

        $file = 'fileData.txt';
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'fileData.txt'
        );
        return $response;
    }
}
