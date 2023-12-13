<?php

namespace App\Controller;

use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function payment(Request $request)
    {
        Stripe::setApiKey($this->getParameter('secret_key'));

        return $this->render('payment/index.html.twig');
    }

    #[Route(path: '/process-payment', name: 'process_payment')]
    public function processPayment(Request $request, Security $security)
    {
        // Récupération de la route
        $domain = 'https://' . $request->getHost() . '/admin';

        // Obtention du produit
        $product = $this->em->getRepository(Products::class)->find(1);

        // Obtention des informations de l'utilisateur
        $user = $security->getUser();

        // Obtention de la clé secrète Stripe à partir des paramètres
        $secretKey = $this->getParameter('secret_key');

        // Configuration de la clé secrète Stripe
        \Stripe\Stripe::setApiKey($secretKey);
        header('Content-Type: application/json');

        // Création de la méthode de paiement
        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
              'price' => 'price_1OMzWdKlM13oW2f4j456V3b0',
              'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $domain . '/payment-success',
            'cancel_url' => $domain . '/cancel',
        ]);
        
        // Traitez la réponse de la charge, enregistrez les données si nécessaire, etc.
        return $this->redirect($checkout_session->url);
    }

    private function getStripeCustomer($user)
    {
        if ($user->getStripeCustomerId()) {
            // Si le client Stripe existe déjà, récupérez-le
            return \Stripe\Customer::retrieve($user->getStripeCustomerId());
        } else {
            // Sinon, créez un nouveau client Stripe
            $customer = \Stripe\Customer::create([
                'email' => $user->getEmail(),
            ]);

            // Enregistrez l'ID du client Stripe dans votre entité utilisateur Symfony
            $user->setStripeCustomerId($customer->id);
            $this->em->persist($user);
            $this->em->flush();

            return $customer;
        }
    }
}
