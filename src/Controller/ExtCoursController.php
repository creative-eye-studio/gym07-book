<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\ExtCoursType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExtCoursController extends AbstractController
{
    private $em;
    private $coursRepo;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->coursRepo = $this->em->getRepository(Cours::class);
    }

    #[Route('/admin/cours', name: 'app_ext_cours')]
    public function index(): Response
    {
        return $this->render('ext_cours/index.html.twig', [
            'controller_name' => 'ExtCoursController',
        ]);
    }

    #[Route('/admin/cours/ajout', name: 'app_ext_cours_create')]
    public function addCours(Request $request): Response
    {
        $cours = new Cours();
        $form = $this->createForm(ExtCoursType::class, $cours);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $this->em->persist($cours);
            $this->em->flush();
        }

        return $this->render('ext_cours/form-manager.html.twig', [
            'form' => $form->createView(),
            'titre' => "Ajouter un cours"
        ]);
    }

    #[Route('/admin/cours/modif/{id}', name: 'app_ext_cours_update')]
    public function updateCours(Request $request, int $id)
    {
        $cours = $this->coursRepo->find($id);
        $form = $this->createForm(ExtCoursType::class, $cours);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $this->em->persist($cours);
            $this->em->flush();
        }

        return $this->render('ext_cours/form-manager.html.twig', [
            'form' => $form->createView(),
            'titre' => "Modifier un cours"
        ]);
    }
}
