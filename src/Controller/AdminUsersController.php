<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\UserService;

class AdminUsersController extends AbstractController
{
    private $userService;
    
    function __construct(UserService $userService){
        $this->userService = $userService;
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function index(): Response
    {
        dump($this->userService->getUsersCMS());

        return $this->render('admin_users/index.html.twig', [
            'users' => $this->userService->getUsersCMS(),
        ]);
    }

    #[Route('/admin/users/modify/{id}', name: 'admin_users_modify')]
    public function update(Request $request, int $id): Response
    {
        return $this->userService->updateUser($request, $id);
    }

    #[Route('/admin/users/delete/{id}', name: 'admin_users_delete')]
    public function delete(Request $request, String $id)
    {
        $this->userService->deleteUser($id);
        // Retour à la liste
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}