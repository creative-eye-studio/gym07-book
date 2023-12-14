<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExtProfileController extends AbstractController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/admin/profile', name: 'app_ext_profile')]
    public function index(): Response
    {
        $token = $this->tokenStorage->getToken();

        if ($token) {
            $user = $token->getUser();
        }

        return $this->render('ext_profile/index.html.twig', [
            'nom' => $user->getLastname(),
            'prenom' => $user->getFirstname(),
            'tel' => $user->getTelephone(),
            'email' => $user->getEmail(),
            'credits' => $user->getCredits(),
            'reservations' => $user->getReservations(),
            'free_courses' => $user->getFreeCourses(),
        ]);
    }
}
