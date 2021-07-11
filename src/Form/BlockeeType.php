<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class BlockeeType extends AbstractType
{
    private $security;
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $licensePlateRepository = $this->em->getRepository('App:LicensePlate');
        //dd($licensePlateRepository);
        $cars = $licensePlateRepository->findBy(['user' => $this->security->getUser()]);
        //dd($cars);
        if(count($cars) == 1)
        {
            $place = $cars[0].'';
            $builder->add('blockee', TextType::class, array('disabled' => true, 'attr' => array('placeholder' => $place)));
        }
        else
        {
            $builder
                ->add('blockee', EntityType::class, [
                    'class' => LicensePlate::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->andWhere('u.user = :val')
                            ->setParameter('val', $this->security->getUser());
                    },
                    'choice_label' => 'license_plate',
                ]);
        }
        $builder->add('blocker');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
