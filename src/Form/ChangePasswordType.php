<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;

class ChangePasswordType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', RepeatedType::class, ['type' => EmailType::class,
                'invalid_message' => 'The email must match.',
                'required' => true,
                'second_options'  => ['label' => false, 'data' => $this->security->getUser()->getUserIdentifier(), 'attr' => array('hidden' => true,
                )],
                'first_options' => ['label' => 'Email'],
            ])
            ->add('old_password', PasswordType::class, array('mapped' => false,
                    'label' => 'Old password',
                    'validation_groups' => array('Default'),
                    'constraints' => new UserPassword(array('message' => 'The old password does not match',))
            ))
            ->add('new_password', RepeatedType::class, ['type' => PasswordType::class, 'mapped' => false,
                'constraints' => [new Length(['min' => 3, 'max' => 10])],
                'invalid_message' => 'The new password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'New Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
