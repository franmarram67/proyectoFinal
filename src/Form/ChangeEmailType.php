<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Types
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

// Constraints
use Symfony\Component\Validator\Constraints\EqualTo;

use Symfony\Component\Security\Core\Security;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->security->getUser();
        $builder
            ->add('oldEmail', EmailType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new EqualTo([
                        'message' => 'This value should be equal to your current email',
                        'value' => $user->getEmail(),
                    ]),
                ],
            ])
            ->add('newEmail', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'The email fields must match.',
                'options' => ['attr' => ['class' => 'email-field']],
                'required' => true,
                'first_options'  => ['label' => 'Email'],
                'second_options' => ['label' => 'Repeat Email'],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
}
