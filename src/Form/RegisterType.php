<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
                'label' => "Nom",
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('firstname', TextType::class, [
                'label' => "Prénom",
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('telephone', TextType::class, [
                'label' => "Téléphone",
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('date_debut_adh', DateType::class, [
                'label' => "Date de début d'adhésion",
                'widget' => 'single_text',
                'required' => false,
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('date_fin_adh', DateType::class, [
                'label' => "Date de fin d'adhésion",
                'widget' => 'single_text',
                'required' => false,
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('payment_type', ChoiceType::class, [
                'label' => "Type de paiement",
                'choices' => [
                    'Chèque' => 'Chèque',
                    'CB' => 'CB',
                    'Prélèvement' => 'Prélèvement',
                    'Offert' => 'Offert',
                    'Espèces' => 'Espèces',
                ],
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Forfait annuel' => 'ROLE_ANNUEL',
                    'Forfait 10 sessions' => 'ROLE_10SESSIONS',
                    'Forfait 1 séance / semaine' => 'ROLE_DECOUVERTE',
                    'Forfait 6 mois' => 'ROLE_6MOIS',
                    'Forfait 3 mois' => 'ROLE_3MOIS',
                    'Forfait 1 mois' => 'ROLE_1MOIS',
                    'Forfait 1 séance' => 'ROLE_1SEANCE',
                    'Forfait Etudiant / Senior' => 'ROLE_ETU_SEN',
                    'Forfait Pompiers / Gendarmes / Police' => 'ROLE_FONCTIONNAIRE',
                    'Invité' => 'ROLE_BASE',
                ],
                'label' => "Type d'adhésion",
                'mapped' => false,
                'row_attr' => [
                    'class' => "mb"
                ] 
            ])
            ->add('email', EmailType::class, [
                'label' => "E-Mail",
                'row_attr' => [
                    'class' => "mb"
                ] 
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
