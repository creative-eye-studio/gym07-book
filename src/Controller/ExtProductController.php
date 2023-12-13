<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExtProductController extends AbstractController
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin/product/unit/{idPlan}', name: 'app_product_unit')]
    public function productUnit(int $idPlan): Response
    {
        Stripe::setApiKey($this->getParameter('secret_key'));

        $plan = $this->em->getRepository(Planning::class)->find($idPlan);
        
        $form = $this->createForm(PaymentType::class);

        return $this->render('ext_product/index.html.twig', [
            'plan' => $plan,
            'stripe_public_key' => $this->getParameter('public_key'),
            'form' => $form->createView(),
        ]);
    }
}
