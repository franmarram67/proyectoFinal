<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Types
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

// Entidades
use App\Entity\User;

class FinishTournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstPlace', EntityType::class, [
                'class' => User::class,
                'required' => true, 'mapped' => true,
            ])
            ->add('secondPlace', EntityType::class, [
                'class' => User::class,
                'required' => true, 'mapped' => true,
            ])
            ->add('thirdPlace', EntityType::class, [
                'class' => User::class,
                'required' => true, 'mapped' => true,
            ])
            ->add('fourthPlace', EntityType::class, [
                'class' => User::class,
                'required' => true, 'mapped' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
