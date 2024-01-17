<?php

namespace App\Services;

use App\Entity\Reservations;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


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
                $user->setCredits($user->getCredits() + 1);
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        // Envoie d'un email au personnes en attente
        $resas = $this->em->getRepository(Reservations::class)->findBy([
            'id' => $id,
            'etat' => '0'
        ]);

        foreach ($resas as $resa) {
            $email = (new TemplatedEmail())
                ->from('no-reply@lasallecrossfit.fr')
                ->to($resa->getUser()->getEmail())
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

    private function sendMail(String $subject, String $template, array $variables) {
    }
}
