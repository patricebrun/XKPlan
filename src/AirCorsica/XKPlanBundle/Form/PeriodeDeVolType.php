<?php

namespace AirCorsica\XKPlanBundle\Form;

use AirCorsica\XKPlanBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodeDeVolType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //$defaultHeure = new \DateTime;
        //$defaultHeure->setTime(14,00);

        $builder
            ->add('dateDebut',DatePickerType::class,array(
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'form-control input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-type' => 'debut',
                    'data-date-format' => 'dd-mm-yyyy',
                    'style' => 'width: 100px;'
                ]
            ))
            ->add('dateFin',DatePickerType::class,array(
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'form-control input-inline datepicker select-bg',
                    'data-provide' => 'datepicker',
                    'data-type' => 'fin',
                    'data-date-format' => 'dd-mm-yyyy',
                    'style' => 'width: 100px;'
                ]
            ))
            ->add('decollage',TimeType::class,array(
                'widget' => 'single_text',
               'html5' => false,
               // 'data' => null,
                'attr' => [
                    'class' => 'select-bg',
                ]
            ))
            ->add('atterissage',TimeType::class,array(
                'widget' => 'single_text',
                'html5' => false,
                //'data' => null,
                'attr' => [
                    'class' => 'select-bg'
                ]
            ))
            ->add('joursDeValidite',ChoiceType::class,array(
                    'multiple'=> true,
                    'expanded' => true,
                    'label' => 'Jours de validité',
                    'choices' => [
                        '1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7
                    ],
                    'attr' => [
                        'class' => 'h-show'
                    ]
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'key'         => '',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\PeriodeDeVol'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_periodedevol';
    }


}
