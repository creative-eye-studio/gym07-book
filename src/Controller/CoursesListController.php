<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Entity\Reservations;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoursesListController extends AbstractController
{
    private $em;
    private $hours;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->hours = $this->em->getRepository(Planning::class);
    }


    // Liste des horaires
    // -----------------------------------------------------------
    #[Route('/admin/courses-list', name: 'app_courses_list')]
    public function index(): Response
    {
        return $this->render('courses_list/index.html.twig', [
            'wod' => $this->getHours(1),
            'wodInit' => $this->getHours(2)
        ]);
    }

    public function getHours(int $id)
    {
        $hours = $this->hours->findBy(['cours' => $id], ['dateTimeStart' => 'ASC']);
        return $hours;
    }


    // Participants à un horaire
    // -----------------------------------------------------------
    #[Route('/admin/participants/{id}', name: 'app_participants_list')]
    public function participants(int $id)
    {
        $hour = $this->hours->find($id);

        return $this->render('courses_list/participants.html.twig', [
            'title' => $hour->getCours()->getNomCours(),
            'datestart' => $hour->getDateTimeStart(),
            'dateend' => $hour->getDateTimeEnd(),
            'list' => $hour->getReservations(),
        ]);
    }

    #[Route('/admin/participants/{id}/{action}', name: 'app_customer_action', requirements: ['action' => 'accept|refuse|waiting'])]
    public function handleCustomerAction(Request $request, int $id, string $action): Response
    {
        $resa = $this->em->getRepository(Reservations::class)->find($id);

        switch ($action) {
            case 'accept':
                $etat = 1;
                break;
            case 'refuse':
                $etat = 2;
                break;
            case 'waiting':
                $etat = 0;
                break;
            default:
                throw $this->createNotFoundException('Action non valide.');
        }

        $resa->setEtat($etat);
        $this->em->persist($resa);
        $this->em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}
