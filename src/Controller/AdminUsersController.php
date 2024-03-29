<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Entity\Reservations;
use App\Entity\User;
use App\Form\RegisterCourseType;
use App\Form\UserSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;

class AdminUsersController extends AbstractController
{
    private $userService;
    
    function __construct(UserService $userService){
        $this->userService = $userService;
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(UserSearchType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            
        }

        return $this->render('admin_users/index.html.twig', [
            'users' => $this->userService->getUsersCMS(),
            'form' => $form->createView(),
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

    #[Route('/admin/users/regist-course/{id}', name: 'admin_users_regist')]
    public function registCourse(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        $resa = new Reservations();
        $form = $this->createForm(RegisterCourseType::class, $resa);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $data = $form->getData();
            if ($user->getFreeCourses() > 0 || $user->getCredits() > 0) {
                // Création de la réservation
                $plan = $data->getPlanning();
                $planning = $em->getRepository(Planning::class)->find($plan->getId());
                $resa->setUser($user);
                $resa->setPlanning($planning);
                $resa->setDateResa(new \DateTime());
                $resa->setEtat(1);
                $resa->setUnit(false);
    
                $user->setLastRegister(new \DateTime());

                if ($user->getFreeCourses() > 0) {
                    $user->setFreeCourses($user->getFreeCourses() - 1);
                }
    
                $rolesToExclude = ['ROLE_ANNUEL', 'ROLE_ADMIN', 'ROLE_6MOIS', 'ROLE_3MOIS', 'ROLE_1MOIS', 'ROLE_ETU_SEN', 'ROLE_FONCTIONNAIRE'];
                if (count(array_intersect($rolesToExclude, $user->getRoles())) === 0) {
                    if ($user->getFreeCourses() == 0 && $user->getCredits() > 0) {
                        $user->setCredits($user->getCredits() - $plan->getCours()->getCredits());
                    }
                }
                
                $em->persist($resa);
                $em->persist($user);
                $em->flush();
            }
        }

        return $this->render('ext_profile/index.html.twig', [
            'id' => $user->getId(),
            'nom' => $user->getLastname(),
            'prenom' => $user->getFirstname(),
            'tel' => $user->getTelephone(),
            'email' => $user->getEmail(),
            'credits' => $user->getCredits(),
            'roles' => $user->getRoles()[0],
            'reservations' => $user->getReservations(),
            'payment_type' => $user->getPaymentType(),
            'free_courses' => $user->getFreeCourses(),
            'form' => $form->createView(),
        ]);
    }

}