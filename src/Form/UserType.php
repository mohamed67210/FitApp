<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            // ->add('email', EmailType::class)
            // ->add('password', PasswordType::class)
            ->add('prenom', TextType::class, ['label' => 'Votre Prénom', 'attr' => ['class' => 'form-control']])
            ->add('nom', TextType::class, ['label' => 'Votre Nom', 'attr' => ['class' => 'form-control']])
            ->add('image', FileType::class, ['label'=>'Votre image de profile','attr' => ['class' => 'form-control'],'required' => false, 'mapped' => false, 'constraints' => [
                new File([
                    'maxSize' => '5000k',
                    'mimeTypes' => [
                        'image/jpeg',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide',
                ])
            ],])
            // ->add('biographie', TextareaType::class, ['required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn btn-success']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
