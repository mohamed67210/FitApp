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
        $payload = $request->getContent();
        $signature = 'whsec_030fc7522b9a5b362683cf36f5627fea6f91625e6cc56342dd6ca75686ef2a7d';
        $headerSignature = $request->headers->get('Stripe-Signature');
        dd($request->headers->all());

        // Vérifiez la signature pour vous assurer que la demande provient de Stripe
        // Utilisez la clé secrète Stripe correspondante à votre mode (test ou production)
        $secretKey = 'sk_test_51MyBkxH7jmQ7y8JFjhlj5nkQbrcZlmFYQTuIJ1s8wjxBbm2U8oy9MzpfT3I7b437smvqQYR9pvKPdpuKAeOlxlT400XvRAT6Yc';

        // Traitez l'événement en fonction de son type
        $event = \Stripe\Webhook::constructEvent($payload, $signature, $secretKey);
        // switch ($event->type) {
        //     case 'charge.succeeded':
        //         // Récupérez les informations pertinentes de l'événement Stripe
        //         $amount = $event->data->object->amount / 100; // Conversion du montant en centimes
        //         $items = $event->data->object->display_items;
        //         $billingEmail = $event->data->object->billing_details->email;
        //         $billingCountry = $event->data->object->billing_details->address->country;
        //         // creer une nouvelle commande
        //         $commande = new Commande;
        //         $commande->setMontant($amount);
        //         $commande->setUser($this->getUser());
        //         $commande->setAdresseFacturation($billingEmail);
        //         $commande->setPaysFacturation($billingCountry);
        //         // Créez un objet DateTime
        //         $dateTime = new \DateTime();
        //         // Convertissez l'objet DateTime en objet DateTimeImmutable
        //         $dateTimeImmutable = \DateTimeImmutable::createFromMutable($dateTime);
        //         // Utilisez l'objet DateTimeImmutable dans la méthode setCreateAt
        //         $commande->setCreateAt($dateTimeImmutable);
        //         // Persistez la commande dans la base de données
        //         $entityManager = $doctrine->getManager();
        //         $entityManager->persist($commande);
        //         $entityManager->flush();
        //         break;
        //     case 'invoice.paid':
        //         //         // Traitez l'événement de facture payée
        //         break;
        //         //     // Ajoutez des cas supplémentaires pour d'autres types d'événements
        //     default:
        //         //         // Événement non pris en charge
        //         break;
        // }
        return new Response('Webhook handled', 200);
    }
}
