<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Planning;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtPlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cours', EntityType::class, [
                'class' => Cours::class,
                'choice_label' => 'nom_cours',
                'label' => "Cours",
                'attr' => [
                    'class' => "mb"
                ]
            ])
            ->add('dateTimeStart', DateTimeType::class, [
                'label' => "Heure de début du cours",
                'widget' => 'single_text',
                'input' => 'datetime',
                'input_format' => "d-m-Y",
                'attr' => [
                    'class' => "mb"
                ]
            ])
            ->add('dateTimeEnd', DateTimeType::class, [
                'label' => "Heure de fin du cours",
                'widget' => 'single_text',
                'input' => 'datetime',
                'input_format' => "d-m-Y",
                'attr' => [
                    'class' => "mb"
                ]
            ])
            ->add('places', NumberType::class, [
                'label' => "Nombre de places",
                'attr' => [
                    'class' => "mb"
                ]
            ])
            ->add('task', SubmitType::class, [
                'label' => "Envoyer"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
}
