<?php

namespace App\Controller;

use App\Entity\Planning;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $em;
    private $planningRepo;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->planningRepo = $this->em->getRepository(Planning::class);
    }

    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route(path: '/api/planning', name: 'api_planning')]
    public function Apiplanning()
    {
        $courses = $this->planningRepo->findAll();

        $array = array_map(function ($course) {
            return [
                'id' => $course->getId(),
                'title' => $course->getCours()->getNomCours() . " | " . $course->getDateTimeStart()->format("H:i") . " - " . $course->getDateTimeEnd()->format("H:i"),
                'start' => $course->getDateTimeStart()->format("Y-m-d"),
                'url' => '/admin/plan/' . $course->getId()
            ];
        }, $courses);

        return $this->json($array);
    }
}
