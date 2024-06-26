<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Entity\Reservations;
use App\Form\ExtPlanningType;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Date;

class ExtPlanningController extends AbstractController
{
    private $tokenStorage;
    private $em;
    private $planningRepo;
    private $resaRepo;
    private $mailer;

    function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, MailerInterface $mailer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->planningRepo = $this->em->getRepository(Planning::class);
        $this->resaRepo = $this->em->getRepository(Reservations::class);
    }

    #[Route('/admin/plan/{id}', name: 'app_ext_planning')]
    public function planning(int $id): Response
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            $roles = $user->getRoles();
            $lastRegister = $user->getLastRegister()?->format("Y-m-d") ?? "";
            $creditsUser = $user->getCredits();
            $freeCourses = $user->getFreeCourses();
        }

        $plan = $this->planningRepo->find($id);

        $resas = $this->resaRepo->findBy([
            'etat' => 1,
            'planning' => $plan
        ]);

        $resaUser = $this->resaRepo->findOneBy([
            'planning' => $plan,
            'user' => $user
        ]);

        $dontInsc = $resaUser ? true : false;

        // Périodes d'inscription
        $dateToday = new \DateTime();
        $limitTimeAdh = $user->getDateFinAdh();
        $endedAdh = $dateToday > $limitTimeAdh;
        $endAdh = $endedAdh ? true : false;
        
        $diffDays = $this->compareDates(date("Y-m-d"), $lastRegister) ?? null;

        return $this->render('ext_planning/index.html.twig', [
            'id' => $plan->getId(),
            'titre' => $plan->getCours()->getNomCours(),
            'description' => $plan->getCours()->getDescription(),
            'credits' => $plan->getCours()->getCredits(),
            'date' => $plan->getDateTimeStart()->format("d-m-Y"),
            'heureDebut' => $plan->getDateTimeStart()->format("H:i"),
            'heureFin' => $plan->getDateTimeEnd()->format("H:i"),
            'resaCount' => count($resas),
            'places' => $plan->getPlaces(),
            "diffDays" => $diffDays,
            'lastRegister' => $lastRegister,
            'role' => $roles[0],
            "creditsUser" => $creditsUser,
            'freeCourses' => $freeCourses,
            'dontInsc' => $dontInsc,
            'endedAdh' => $limitTimeAdh,
            'endAdh' => $endAdh
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

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('ext_planning/form-manager.html.twig', [
            'titre' => "Ajouter un horaire",
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/planning/update/{id}', name: 'app_ext_planning_update')]
    public function updatePlanning(Request $request, int $id): Response
    {
        $planning = $this->planningRepo->find($id);

        $form = $this->createForm(ExtPlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($planning);
            $this->em->flush();

            return $this->redirectToRoute('app_ext_planning', ['id' => $planning->getId()]);
        }

        return $this->render('ext_planning/form-manager.html.twig', [
            'titre' => "Modifier un horaire",
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
        $verifResa = $this->em->getRepository(Reservations::class)->findOneBy([
            'user' => $user,
            'planning' => $plan
        ]);

        if (!$verifResa) {
            $resa = new Reservations();
            $resa->setUser($user);
            $resa->setPlanning($plan);
            $resa->setDateResa(new \DateTime());
            $resa->setEtat($nbResa >= $places ? 0 : 1);
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

            $variables = [
                'courseName' => $resa->getPlanning()->getCours()->getNomCours(),
                'courseDate' => $resa->getPlanning()->getDateTimeStart()->format('d/m/Y à H:i'),
            ];

            if ($nbResa >= $places) {
                $this->sendMail(
                    "Mise en attente de votre inscription à un cours", 
                    "emails/waiting-course.html.twig",
                    $variables);
            } else {
                $this->sendMail(
                    "Validation de votre inscription à un cours", 
                    "emails/valid-course.html.twig", 
                    $variables);
            }

            $this->em->persist($resa);
            $this->em->persist($user);
            $this->em->flush();
        }


        return new RedirectResponse($this->generateUrl('app_ext_profile'));
    }

    #[Route('/admin/planning/delete/{id}', name: 'app_ext_planning_delete')]
    public function deleteCourse(int $id)
    {
        $plan = $this->planningRepo->find($id);
        $resas = $this->em->getRepository(Reservations::class)->findAll();

        foreach ($resas as $resa) {
            if ($resa->getPlanning() == $plan) {
                $this->em->remove($resa);
            }
        }

        $this->em->remove($plan);
        $this->em->flush();

        return $this->redirectToRoute('app_admin');
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

    private function sendMail(String $subject, String $template, array $variables) {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
        }

        $email = (new TemplatedEmail())
            ->from('no-reply@lasallecrossfit.fr')
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($variables);
        
        $this->mailer->send($email);
    }
}
