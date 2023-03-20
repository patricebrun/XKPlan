<?php

namespace AirCorsica\XKPlanBundle\Form;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupeSITAType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir un Nom"
                ),
                'required' => false
            ))
            ->add('groupeGenerique',CheckboxType::class,array(
                'label' => 'Groupe générique',
                'required' => false
            ))
            ->add('aeroportAttache',EntityType::class,array(
                'label' => 'Aéroport d\'attache',
                'class' => 'AirCorsica\XKPlanBundle\Entity\Aeroport',
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir un aéroport"
                ),
                'required' => false
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\GroupeSITA'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_groupesita';
    }


}
