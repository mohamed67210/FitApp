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

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule',TextType::class)
            ->add('description',TextareaType::class)
            ->add('video', FileType::class, ['required' => false, 'mapped' => false, 'constraints' => [
                new File([
                    // 'maxSize' => '5000k',
                    'mimeTypes' => [
                        'video/mp4',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid video',
                ])
            ],])
            // ->add('miniature', FileType::class, ['required' => false, 'mapped' => false, 'constraints' => [
            //     new File([
            //         'maxSize' => '5000k',
            //         'mimeTypes' => [
            //             'image/jpeg',
            //         ],
            //         'mimeTypesMessage' => 'Please upload a valid image',
            //     ])
            // ],])
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
