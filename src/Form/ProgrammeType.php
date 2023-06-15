<?php

namespace App\Form;

use App\Entity\Programme;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ProgrammeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule', TextType::class,[
                'attr'=>['class' => 'form-control']
                    ])
            ->add('description', TextareaType::class, [
                'attr'=>[
                    'class' => 'form-control'
                ],
                'constraints' => [
                new Length([
                    'max' => 1000,
                    'maxMessage' => 'La description ne peut pas dépasser 1000 caractères.'
                ])
            ]])
            ->add('prix', NumberType::class,['attr'=>[
                'class' => 'form-control'
            ]])
            ->add('prixPromo', NumberType::class, [
                'attr'=>['class' => 'form-control'],
                'required'   => false,
            ])
            ->add('image', FileType::class, [
                'attr'=>[
                    'class' => 'form-control'
                ],
                'required' => false,
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
            // ->add('isValid', HiddenType::class, [
            //     'attr'=>['class' => 'form-control'],
            //     'data' => '0'])
            ->add('categorie')
            // ->add('coach')
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn_achat']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Programme::class,
        ]);
    }
}
