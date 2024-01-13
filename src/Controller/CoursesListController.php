<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Entity\Reservations;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CoursesListController extends AbstractController
{
    private $em;
    private $hours;
    private $mailer;
    private $tokenStorage;

    function __construct(EntityManagerInterface $em, MailerInterface $mailer, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->hours = $this->em->getRepository(Planning::class);
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
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

        $variables = [
            'courseName' => $resa->getPlanning()->getCours()->getNomCours(),
            'courseDate' => $resa->getPlanning()->getDateTimeStart()->format('d/m/Y à H:M'),
        ];

        switch ($action) {
            case 'accept':
                $etat = 1;
                $this->sendMail(
                    "Validation de votre inscription à un cours", 
                    "emails/valid-course.html.twig", 
                    $variables);
                break;
            case 'refuse':
                $etat = 2;
                $this->sendMail(
                    "Annulation de votre inscription à un cours", 
                    "emails/refused-course.html.twig",
                    $variables);
                break;
            case 'waiting':
                $etat = 0;
                $this->sendMail(
                    "Mise en attente de votre inscription à un cours", 
                    "emails/waiting-course.html.twig",
                    $variables);
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
