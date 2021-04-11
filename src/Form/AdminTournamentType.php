<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminTournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('startDate')
            ->add('finished')
            ->add('creationDate')
            ->add('hidden')
            ->add('videogame')
            ->add('creatorUser')
            ->add('firstPlace')
            ->add('secondPlace')
            ->add('thirdPlace')
            ->add('fourthPlace')
            ->add('players')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
