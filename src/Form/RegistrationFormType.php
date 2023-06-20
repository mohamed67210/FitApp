<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    'Coach' => 'ROLE_COACH'
                ],
            ])
            ->add('image', HiddenType::class, ['data' => 'defaultUser.png',])
            // ->add('nom', TextType::class, ['label' => false,'attr'=>['placeholder'=>'Votre Nom']])
            ->add('prenom', TextType::class, [
                'label' => false,
                'attr'=>['placeholder'=>'Votre Prenom'],
                ])
            ->add('email', EmailType::class, ['label' => false,'attr'=>['placeholder'=>'VotreEmail']])
            ->add('agreeTerms', CheckboxType::class, [
                'label'=>"Lu et accepté ",
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepté nos conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'constraints'=>[
                    new Regex('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
                    'Il faut un mot de passe de 8 caractéres avec 1 lettre majuscule, 1 lettre miniscule, 1 chiffre, 1 caractere spéciale')
                ],
                'type' => PasswordType::class,
                'invalid_message' => "le mot de passe n'est pas identique.",
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => false,'attr'=>['placeholder'=>'Votre mot de passe']],
                'second_options' => ['label' => false,'attr'=>['placeholder'=>'validation de mot de passe']],
                
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
