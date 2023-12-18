<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Entity\Reservations;
use App\Form\ExtPlanningType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExtPlanningController extends AbstractController
{
    private $tokenStorage;
    private $em;
    private $planningRepo;

    function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->planningRepo = $this->em->getRepository(Planning::class);
    }

    #[Route('/admin/plan/{id}', name: 'app_ext_planning')]
    public function planning(int $id): Response
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            $roles = $user->getRoles();
            $lastRegister = $user->getLastRegister() != null ? $user->getLastRegister()->format("Y-m-d") : "";
            $creditsUser = $user->getCredits();
            $freeCourses = $user->getFreeCourses();
        }

        $plan = $this->planningRepo->find($id);

        $diffDays = $this->compareDates(date("Y-m-d"), $lastRegister) ?? null;

        return $this->render('ext_planning/index.html.twig', [
            'id' => $plan->getId(),
            'titre' => $plan->getCours()->getNomCours(),
            'description' => $plan->getCours()->getDescription(),
            'credits' => $plan->getCours()->getCredits(),
            'date' => $plan->getDateTimeStart()->format("d-m-Y"),
            'heureDebut' => $plan->getDateTimeStart()->format("H:i"),
            'heureFin' => $plan->getDateTimeEnd()->format("H:i"),
            'resaCount' => $plan->getReservations()->count(),
            'places' => $plan->getPlaces(),
            "diffDays" => $diffDays,
            'lastRegister' => $lastRegister,
            'role' => $roles[0],
            "creditsUser" => $creditsUser,
            'freeCourses' => $freeCourses
        ]);
    }

    #[Route('/admin/planning/ajout', name: 'app_ext_planning_add')]
    public function addPlanning(Request $request): Response
    {
        $planning = new Planning();

        $form = $this->createForm(ExtPlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($planning);
            $this->em->flush();
        }

        return $this->render('ext_planning/form-manager.html.twig', [
            'titre' => "Ajouter un horaire",
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/planning/register/{idPlan}', name: 'app_ext_planning_register')]
    public function registPlanning(int $idPlan): Response
    {
        $token = $this->tokenStorage->getToken();

        $user = $token->getUser();
        $plan = $this->planningRepo->find($idPlan);
        $places = $plan->getPlaces();

        $nbResa = $plan->getReservations()->count();

        // Création de la réservation
        $resa = new Reservations();
        $resa->setUser($user);
        $resa->setPlanning($plan);
        $resa->setDateResa(new \DateTime());
        $resa->setEtat($nbResa >= $places ? 0 : 1);
        $resa->setUnit(false);

        $user->setLastRegister(new \DateTime());

        if ($user->getFreeCourses() > 0) {
            $user->setFreeCourses($user->getFreeCourses() - 1);
        } elseif ($user->getCredits() > 0) {
            $rolesToExclude = ['ROLE_ANNUEL', 'ROLE_ADMIN', 'ROLE_6MOIS', 'ROLE_3MOIS', 'ROLE_1MOIS', 'ROLE_ETU_SEN', 'ROLE_FONCTIONNAIRE'];
            if (count(array_intersect($rolesToExclude, $user->getRoles())) === 0) {
                $user->setCredits($user->getCredits() - 1);
            }
        } else {
            return false;
        }

        $this->em->persist($resa);
        $this->em->persist($user);
        $this->em->flush();


        return new RedirectResponse($this->generateUrl('app_ext_profile'));
    }

    function compareDates($date1, $date2)
    {
        // Convertissez les chaînes de date en objets DateTime
        $dateTime1 = new DateTime($date1);
        $dateTime2 = new DateTime($date2);

        // Calculez la différence entre les deux dates
        $difference = $dateTime1->diff($dateTime2);

        // Comparez le nombre de jours de différence
        return $difference->days > 7;
    }
}
