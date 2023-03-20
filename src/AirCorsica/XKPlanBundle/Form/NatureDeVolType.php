<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\ResetType;

class NatureDeVolType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('nom')->add('ordre')->add('dateTimeModification')->add('dateTimeCreation')        ;
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir Nom"
                ),
                'required' => false
            ))
            ->add('ordre', IntegerType::class, array(
                'label' => 'Ordre',
                'attr' => array(
                    'placeholder'       => "Saisir ordre liste"
                ),
                'required' => false,
                'empty_data' => '0',
            ))
            //->add('save', SubmitType::class, array('label' => 'Enregistrer'))
            //->add('cancel', ResetType::class, array('label' => 'Reset'))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\NatureDeVol'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_naturedevol';
    }


}
