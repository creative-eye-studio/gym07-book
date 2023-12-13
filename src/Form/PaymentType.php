<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Numéro de carte',
                'row_attr' => [
                    'class' => "mb-4"
                ]
            ])
            ->add('expMonth', TextType::class, [
                'label' => 'Mois d\'expiration',
                'row_attr' => [
                    'class' => "mb-4"
                ]
            ])
            ->add('expYear', TextType::class, [
                'label' => 'Année d\'expiration',
                'row_attr' => [
                    'class' => "mb-4"
                ]
            ])
            ->add('cvc', TextType::class, [
                'label' => 'CVC',
                'row_attr' => [
                    'class' => "mb-4"
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Passer la commande',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
