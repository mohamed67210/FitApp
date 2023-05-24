<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,['label'=>false,'attr'=>['placeholder'=>'Chercher un coach par nom...','class' => 'search-input']])
            ->add('submit',SubmitType::class,['label'=>'<i class="fa-solid fa-magnifying-glass"></i>','attr'=>['class' => 'search-btn'],'label_html' => true,])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'attr' => [
                'class' => 'search-form',
            ]
        ]);
    }
}
