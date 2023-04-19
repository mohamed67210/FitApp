<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération de la liste des pays via l'API
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://restcountries.com/v3.1/all');
        $countries = $response->toArray();
        // Transformation de la liste des pays pour l'utiliser dans le select
        $choices = [];
        foreach ($countries as $country) {
            $choices[$country['name']['common']] = $country['name']['common'];
        }
        // dd($options['user']);
        $builder
            ->add('adresseFacturation', EmailType::class, ['label' => false, 'attr' => ['class' => 'form-control']])
            ->add('paysFacturation', ChoiceType::class, ['label' => false, 'attr' => ['class' => 'form-select form-select-lg mb-3'],'choices' => $choices])
            ->add('submit', SubmitType::class, ['label' => 'Valider', 'attr' => ['class' => 'btn btn-primary']]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'user' => []
        ]);
    }
}
