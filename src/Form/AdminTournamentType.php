<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminTournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('places', ChoiceType::class, [
                'choices' => [
                    '8' => 8,
                    '16' => 16,
                    '24' => 24,
                    '32' => 32,
                ]
            ])
            ->add('startDate')
            ->add('finished')
            ->add('hidden')
            ->add('videogame')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
