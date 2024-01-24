<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $em;
    private $planningRepo;
    private $userService;

    function __construct(EntityManagerInterface $em, UserService $userService)
    {
        $this->em = $em;
        $this->planningRepo = $this->em->getRepository(Planning::class);
        $this->userService = $userService;
    }

    #[Route(path: '/api/users/{term}', name: 'api_users')]
    public function ApiUsers(string $term): JsonResponse
    {
        $datas = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'name' => $user->getFirstname() . " " . $user->getLastname()
            ];
        }, $this->userService->getUsersCMS());
    
        // Filtrer les données en fonction du terme
        $filteredDatas = array_filter($datas, function($data) use ($term) {
            return stripos($data['name'], $term) !== false;
        });

        return $this->json($filteredDatas, 200);
    }

    #[Route(path: '/api/planning', name: 'api_planning')]
    public function ApiPlanning(): JsonResponse
    {
        $array = array_map(function ($course) {
            return [
                'id' => $course->getId(),
                'title' => $course->getDateTimeStart()->format("H:i") . " - " . $course->getDateTimeEnd()->format("H:i") . " - " . $course->getCours()->getNomCours(),
                'start' => $course->getDateTimeStart()->format("Y-m-d"),
                'url' => '/admin/plan/' . $course->getId()
            ];
        }, $this->planningRepo->findAll());

        return $this->json($array);
    }

    #[Route(path: '/api/planning-admin', name: 'api_planning_admin')]
    public function ApiAdminPlanning(): JsonResponse
    {
        $array = array_map(function ($course) {
            return [
                'id' => $course->getId(),
                'title' => $course->getDateTimeStart()->format("H:i") . " - " . $course->getDateTimeEnd()->format("H:i") . " (" . $course->getReservations()->count() . "/" . $course->getPlaces() . ") | " . $course->getCours()->getNomCours(),
                'start' => $course->getDateTimeStart()->format("Y-m-d"),
                'url' => '/admin/participants/' . $course->getId()
            ];
        }, $this->planningRepo->findAll());

        return $this->json($array);
    }
}
