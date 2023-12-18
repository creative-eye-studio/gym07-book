<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, [
                'label' => "Nom", 
                'row_attr' => [
                    'class' => 'mb'
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => "Prénom", 
                'row_attr' => [
                    'class' => 'mb'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => "E-Mail", 
                'row_attr' => [
                    'class' => 'mb'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => [
                    'label' => 'Mot de passe', 
                    'row_attr' => [
                        'class' => 'mt mb'
                    ]
                ],
                'second_options' => ['label' => 'Confirmation du mot de passe']
            ])
            ->add('telephone', TelType::class, [
                'label' => "Téléphone", 
                'row_attr' => [
                    'class' => 'mt mb'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Envoyer"
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
