<?php

namespace App\Form;

use App\Entity\Module;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule',TextType::class,[
                'required' => true,
                'attr'=>['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => "L'intitulé ne doit pas dépasser 50 caractères."
                    ])
                ]
                ])
            ->add('description',TextareaType::class,[
                'required' => true,
                'attr'=>['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => "La description ne doit pas dépasser 255 caractères."
                    ])
                ]
                ])
            ->add('video', FileType::class, ['required' => true, 'constraints' => [
                new File([
                    'maxSize' => '500000k',
                    'mimeTypes' => [
                        'video/mp4',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid video',
                ])
            ],])
            // ->add('programme')
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn_achat']]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}
