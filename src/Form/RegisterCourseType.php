<?php

namespace App\Form;

use App\Entity\Planning;
use App\Entity\Reservations;
use App\Repository\PlanningRepository;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterCourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('planning', EntityType::class, [
                'label' => "Sélection du cours",
                'class' => Planning::class,
                'query_builder' => function (PlanningRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.dateTimeStart', 'ASC'); // Supposons que 'nom' est le champ par lequel vous souhaitez trier
                },
                'choice_label' => 'formattedCreatedAt',
                'choice_value' => 'id',
                'group_by' => 'cours.nom_cours',
                'attr' => [
                    'class' => 'mb-3'
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
            'data_class' => Reservations::class,
        ]);
    }
}
