<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Planning;
use App\Entity\Products;
use App\Entity\Reservations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PaymentController extends AbstractController
{

    private $em;
    private $tokenStorage;

    function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function payment(Security $security)
    {
        $user = $security->getUser();

        if ($user && $user->isPaymentSuccess()) {

            // Réinitialiser le statut de paiement à false après le traitement réussi
            $user->setPaymentSuccess(false);
            $this->em->persist($user);
            $this->em->flush();

            return $this->render('payment/index.html.twig');
        } else {
            throw new NotFoundHttpException('Aucun paiement n\'a été effectué.');
        }
    }

    #[Route(path: '/process-payment/{idPlan}', name: 'process_payment')]
    public function processPayment(Request $request, Security $security, int $idPlan)
    {
        // Récupération de la route
        $domain = 'https://' . $request->getHost();

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
            'success_url' => $domain . '/check-payment/' . $idPlan,
            'cancel_url' => $domain . '/cancel',
        ]);
        
        // Traitez la réponse de la charge, enregistrez les données si nécessaire, etc.
        return $this->redirect($checkout_session->url);
    }

    #[Route(path: '/check-payment/{idPlan}', name: 'check_payment')]
    public function handleStripeWebhook(Security $security, int $idPlan)
    {
        $user = $security->getUser();

        if ($user) {
            // Réservation du cours
            $this->reserveCourse($security, $idPlan);

            // Redirection vers la page de confirmation
            $user->setPaymentSuccess(true);
            $this->em->persist($user);
            $this->em->flush();

            return new RedirectResponse($this->generateUrl('payment_success'));
        }
    }

    private function reserveCourse(Security $security, int $idPlan)
    {
        $resa = new Reservations();
        $plan = $this->em->getRepository(Planning::class)->find($idPlan);
        $nbResa = $plan->getReservations()->count();
        $places = $plan->getPlaces();
        $user = $security->getUser();

        $resa->setPlanning($plan);
        $resa->setUser($user);
        $resa->setDateResa(new \DateTime());
        $resa->setEtat($nbResa >= $places ? 0 : 1);
        $resa->setUnit(true);

        $user->setLastRegister(new \DateTime());

        $this->em->persist($resa);
        $this->em->persist($user);
        $this->em->flush();
    }
}
