<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\NewRegisterType;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use App\Services\FormsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminRegisterController extends AbstractController
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/register', name: 'app_admin_new_register')]
    public function newRegister(Request $request, UserPasswordHasherInterface $encoder, FormsService $formService, JWTService $jwt)
    {
        $user = new User();
        $form = $this->createForm(NewRegisterType::class, $user);
        $form->handleRequest($request);
        $notif = null;

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de l'utilisateur
            $user = $form->getData();
            $password = $encoder->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($password);
            $user->setRoles(['ROLE_BASE']);
            $user->setIsVerified(false);
            $user->setCredits(0);
            $user->setFreeCourses(2);
            $user->setPaymentSuccess(false);
            $user->setPaymentType("Inconnu");

            $this->em->persist($user);
            $this->em->flush();

            // Création du TOKEN
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS2256',
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
            $formService->confirmRegister($user->getEmail(), $user->getFirstName(), $token);
            $notif = "Le compte a bien été crée";

            return $this->redirectToRoute('app_admin_confirm_register');
        }

        return $this->render('admin_register/new-register.html.twig', [
            'form' => $form->createView(),
            'notif' => $notif
        ]);
    }

    #[Route('/admin/register', name: 'app_admin_register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder, FormsService $formService, JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        $notif = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
    
            // Vérifier si un utilisateur existe déjà avec cette adresse e-mail
            $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($existingUser) {
                // Un compte existe déjà avec cette adresse e-mail
                $notif = "Un compte existe déjà avec cette adresse e-mail";
            } else {
                // Enregistrement de l'utilisateur
                $role = $form->get('roles')->getData();
                $user = $form->getData();
                $password = $encoder->hashPassword($user, 'ChangePassword!!!');
                $user->setPassword($password);
                $user->setRoles([$role]);
                $user->setIsVerified(false);

                $roleCreditsMap = [
                    'ROLE_10SESSIONS' => 10,
                    'ROLE_DECOUVERTE' => 4,
                    'ROLE_6MOIS' => 1,
                    'ROLE_3MOIS' => 1,
                    'ROLE_1MOIS' => 1,
                    'ROLE_ETU_SEN' => 1,
                    'ROLE_FONCTIONNAIRE' => 1,
                    'ROLE_ANNUEL' => 1,
                    'ROLE_ADMIN' => 1,
                    // Ajoutez d'autres rôles avec leurs crédits correspondants au besoin
                ];
                
                $user->setCredits($roleCreditsMap[$role] ?? 0);            

                $user->setFreeCourses(2);
                $user->setPaymentSuccess(false);
                $this->em->persist($user);
                $this->em->flush();

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
        }

        return $this->render('admin_register/index.html.twig', [
            'form' => $form->createView(),
            'notif' => $notif
        ]);
    }

    #[Route(path: '/verify/{token}', name: 'verify_user')]
    public function verify_token($token, JWTService $jwt, UserRepository $userRepository): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            //On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $this->em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_login');
            }
        }

        // Ici un problème se pose dans le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/confirm-account', name: 'app_admin_confirm_register')]
    public function confirmRegister()
    {
        return $this->render('reset_password/valid-account.html.twig');
    }
}
