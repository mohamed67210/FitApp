<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, ['label'=>'Donnez votre avis :','attr' => ['class' => "form-label",'placeholder'=>'votre message...'],
            'constraints'=>[
                new Length([
                    'max'=>20,
                    'maxMessage'=>'le message ne doit pas depasser {{ limit }} caractères'
                ])
            ]])
            // ->add('create_at')
            // ->add('user')
            // ->add('programme')
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn_comment']]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
            'attr' => [
                'class' => 'commentaire-form',
            ]
        ]);
    }
}
