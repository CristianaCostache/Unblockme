<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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

}
