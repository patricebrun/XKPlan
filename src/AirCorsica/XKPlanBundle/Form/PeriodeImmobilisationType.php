<?php

namespace AirCorsica\XKPlanBundle\Form;

use AirCorsica\XKPlanBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodeImmobilisationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDebut',DatePickerType::class,array(
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'attr' => [
                    'class' => 'form-control input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-type' => 'debut',
                    'data-saison' => 'false',
                    'data-date-format' => 'dd-mm-yyyy'
                ]
            ))
            ->add('dateFin',DatePickerType::class,array(
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'attr' => [
                    'class' => 'form-control input-inline  datepicker select-bg',
                    'data-provide' => 'datepicker',
                    'data-type' => 'fin',
                    'data-saison' => 'false',
                    'data-date-format' => 'dd-mm-yyyy'
                ]
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation',
            'allow_extra_fields' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_periodeimmobilisation';
    }


}
