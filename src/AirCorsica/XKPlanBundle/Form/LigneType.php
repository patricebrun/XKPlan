<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\ResetType;

class LigneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('dateTimeModification')->add('dateTimeCreation')->add('aeroportDepart')->add('aeroportArrivee')->add('naturesDeVol')        ;

        $builder
            ->add('aeroportDepart', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:Aeroport',
                'choice_label' => 'codeIATA',
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Sélectionner Aéroport Départ"
                ),
                'required' => false,
            ))
            ->add('aeroportArrivee', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:Aeroport',
                'choice_label' => 'codeIATA',
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Sélectionner Aéroport Arrivée"
                ),
                'required' => false,
            ))
            ->add('ordre', TextType::class, array(
                'data' => $options['data']->getOrdre() != "0" ? $options['data']->getOrdre() : "0",
                'required' => true,
            ))
            ->add('naturesDeVol', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:NatureDeVol',
                'choice_label' => 'nom',
                'multiple' => true,
                'attr' => array(
                    'class'       => "select2-multiple",
                    'data-placeholder' => "Sélectionner Natures de vol",
                    'multiple' => "multiple"
                ),
                'required' => false,
            ))
            ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Ligne'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_ligne';
    }


}
