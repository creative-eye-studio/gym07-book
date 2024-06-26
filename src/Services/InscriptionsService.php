<?php

namespace App\Services;

use App\Entity\Reservations;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SendinBlue\Client\Api\TransactionalSMSApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSms;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twilio\Rest\Client;

class InscriptionsService
{
    private $em;
    private $inscriptionRepo;
    private $tokenStorage;
    private $mailer;

    function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, MailerInterface $mailer)
    {
        
        $this->em = $em;
        $this->inscriptionRepo = $this->em->getRepository(Reservations::class);
        $this->tokenStorage = $tokenStorage;
        $this->mailer = $mailer;
    }

    public function cancelInscription(int $id)
    {
        $inscription = $this->inscriptionRepo->find($id);
        $credit = $inscription->getPlanning()->getCours()->getCredits();

        $plan = $inscription->getPlanning();
        $dateStart = $inscription->getPlanning()->getDateTimeStart();
        $token = $this->tokenStorage->getToken();
        $dateActuelle = new DateTime();

        // Vérifier si la différence est de moins de 2 heures
        $diff = $dateActuelle->diff($dateStart);
        $diffMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        if ($dateStart > $dateActuelle && $diffMinutes > 120) {
            if ($token) {
                $user = $token->getUser();
                $user->setCredits($user->getCredits() + $credit);
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        // Envoie d'un email au personnes en attente
        $users = $this->em->getRepository(Reservations::class)->findBy([
            'planning' => $plan,
            'etat' => 0
        ]);

        foreach ($users as $user) {
            // Email
            $email = (new TemplatedEmail())
                ->from('no-reply@lasallecrossfit.fr')
                ->to($user->getUser()->getEmail())
                ->subject("Une place s'est libéré")
                ->htmlTemplate('emails/open-place.html.twig')
                ->context([
                    'cours' => $plan->getCours()->getNomCours(),
                    'date' => $dateStart->format('d/m/Y à h:m')
                ]);
            
            $this->mailer->send($email);
        }
            
        $this->em->remove($inscription);
        $this->em->flush();
    }
}
