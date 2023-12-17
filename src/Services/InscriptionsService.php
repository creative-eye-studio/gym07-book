<?php

namespace App\Services;

use App\Entity\Reservations;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionsService
{
    private $em;
    private $inscriptionRepo;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->inscriptionRepo = $this->em->getRepository(Reservations::class);
    }

    public function cancelInscription(int $id)
    {
        $inscription = $this->inscriptionRepo->find($id);
        $this->em->remove($inscription);
        $this->em->flush();
    }
}
