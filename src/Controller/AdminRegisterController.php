<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use App\Services\FormsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminRegisterController extends AbstractController
{
    #[Route('/admin/register', name: 'app_admin_register')]
    public function index(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $encoder, FormsService $formService, JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        $notif = null;

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de l'utilisateur
            $user = $form->getData();
            $password = $encoder->hashPassword($user, 'ChangePassword!!!');
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(false);
            $doctrine = $doctrine->getManager();
            $doctrine->persist($user);
            $doctrine->flush();

            // Création du TOKEN
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS2256',
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
            $formService->validateRegister($user->getEmail(), $user->getFirstName(), $token);
            $notif = "Le compte a bien été crée";
        }

        return $this->render('admin_register/index.html.twig', [
            'form' => $form->createView(),
            'notif' => $notif
        ]);
    }

    #[Route(path: '/verify/{token}', name: 'verify_user')]
    public function verify_token($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            //On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_login');
            }
        }

        // Ici un problème se pose dans le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
