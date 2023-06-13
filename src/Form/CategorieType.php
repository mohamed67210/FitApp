<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule',TextType::class,[
            'required'=> true,
            'attr'=>['maxlength' => 20,
            'maxMessage' => 'Il faut maximum 20 caractéres',
            'class' => 'form-control']
            ])
            ->add('image', FileType::class, [
                'attr'=>[
                    'class' => 'form-control',
                ],
            'required' => true,
            'mapped' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5000k',
                    'mimeTypes' => [
                        'image/jpeg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image',
                ])
            ],])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                 'attr' => ['class' => 'btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
