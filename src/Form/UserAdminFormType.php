<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse E-Mail'
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Forfait annuel' => 'ROLE_ANNUEL',
                    'Forfait 10 sessions' => 'ROLE_10SESSIONS',
                    'Forfait 10 découverte' => 'ROLE_DECOUVERTE',
                    'Forfait 6 mois' => 'ROLE_6MOIS',
                    'Forfait 3 mois' => 'ROLE_3MOIS',
                    'Forfait 1 mois (sans engagement)' => 'ROLE_1MOIS',
                    'Forfait Etudiant / Senior' => 'ROLE_ETU_SEN',
                    'Forfait Pompiers/Gendarmes/Police' => 'ROLE_FONCTIONNAIRE',
                    'Invité' => 'ROLE_USER',
                ],
                'label' => "Type d'adhésion",
                'mapped' => false
            ])
            ->add('credits', NumberType::class, [
                'label' => "Crédits",
                'html5' => true
            ])
            ->add('remake_pass', CheckboxType::class, [
                'label' => "Modifier le mot de passe",
                'required' => false,
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer'
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
