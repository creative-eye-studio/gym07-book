<?php

namespace App\Services;

use App\Entity\Reservations;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class InscriptionsService
{
    private $em;
    private $inscriptionRepo;
    private $tokenStorage;

    function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        
        $this->em = $em;
        $this->inscriptionRepo = $this->em->getRepository(Reservations::class);
        $this->tokenStorage = $tokenStorage;
    }

    public function cancelInscription(int $id)
    {
        $inscription = $this->inscriptionRepo->find($id);

        $plan = $inscription->getPlanning()->getDateTimeStart();
        $token = $this->tokenStorage->getToken();
        $dateActuelle = new DateTime();

        // Vérifier si la différence est de moins de 2 heures
        $diff = $dateActuelle->diff($plan);
        $diffMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        if ($plan > $dateActuelle && $diffMinutes > 120) {
            if ($token) {
                $user = $token->getUser();
                $user->setCredits($user->getCredits() + 1);
                $this->em->persist($user);
                $this->em->flush();
            }
        } else {
            echo "Action non exécutée : Différence de 2 heures ou moins ou utilisateur non authentifié.";
        }

        $this->em->remove($inscription);
        $this->em->flush();
    }
}
