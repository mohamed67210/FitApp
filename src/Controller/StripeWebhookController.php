<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'app_stripe_webhook')]
    public function handle(ManagerRegistry $doctrine, Request $request): Response
    {
        // Vérification de l'authenticité de la requête Stripe
        $stripeSecretKey = 'sk_test_51MyBkxH7jmQ7y8JFjhlj5nkQbrcZlmFYQTuIJ1s8wjxBbm2U8oy9MzpfT3I7b437smvqQYR9pvKPdpuKAeOlxlT400XvRAT6Yc'; // Remplacez par votre clé secrète Stripe
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $payload = $request->getContent();
        dd($payload);
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $stripeSecretKey);

            // Traitez les événements Stripe en fonction de leur type
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Gérez le paiement validé ici
                    // Exemple : mettez à jour le statut du paiement dans votre base de données
                    break;
                    // Gérez d'autres types d'événements Stripe selon vos besoins

                default:
                    // Gérez les autres types d'événements Stripe si nécessaire
                    break;
            }

            return new Response('Webhook received successfully');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Gestion des erreurs de vérification de la signature
            return new Response('Webhook signature verification failed', Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Gestion des autres exceptions
            return new Response('An error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
