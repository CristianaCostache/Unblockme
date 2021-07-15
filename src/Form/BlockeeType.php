<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Repository\LicensePlateRepository;
use App\Service\LicensePlateService;
use Doctrine\ORM\EntityManagerInterface;
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
    private $licensePlateService;

    public function __construct(Security $security, EntityManagerInterface $em, LicensePlateService $licensePlateService)
    {
        $this->security = $security;
        $this->em = $em;
        $this->licensePlateService = $licensePlateService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->licensePlateService->countLicensePlates($this->security->getUser()) == 1)
        {
            $firstLicensePlate = $this->licensePlateService->getFirstLicensePlate($this->security->getUser());
            $builder->add('blockee', TextType::class, array('disabled' => true, 'attr' => array('placeholder' => $firstLicensePlate)));
        }
        else
        {
            $builder
                ->add('blockee', EntityType::class, [
                    'class' => LicensePlate::class,
                    'query_builder' => function (LicensePlateRepository $er) {
                        return $er->findByUser($this->security->getUser());
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
