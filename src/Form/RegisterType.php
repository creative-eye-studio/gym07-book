<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, [
                'label' => "Nom"
            ])
            ->add('firstname', TextType::class, [
                'label' => "Prénom"
            ])
            ->add('telephone', TextType::class, [
                'label' => "Téléphone"
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Forfait annuel' => 'ROLE_ANNUEL',
                    'Forfait 10 sessions' => 'ROLE_10SESSIONS',
                    'Forfait 10 découverte' => 'ROLE_DECOUVERTE',
                    'Invité' => 'ROLE_USER',
                ],
                'label' => "Type d'adhésion",
                'mapped' => false
            ])
            ->add('email', EmailType::class, [
                'label' => "E-Mail"
            ])
            
            // ->add('password', RepeatedType::class, [
            //     'type' => PasswordType::class,
            //     'invalid_message' => 'Le mot de passe et la confirmation doivent être identiques',
            //     'first_options' => ['label' => 'Votre mot de passe'],
            //     'second_options' => ['label' => 'Confirmer le mot de passe']
            // ])
            
            ->add('submit', SubmitType::class, [
                'label' => "Enregistrer"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
