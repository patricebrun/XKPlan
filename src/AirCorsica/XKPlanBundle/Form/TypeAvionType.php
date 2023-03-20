<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\CallbackTransformer;

class TypeAvionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('version')->add('codeIATA')->add('codeOACI')->add('capaciteSiege')->add('tempsDemiTour')->add('dateTimeModification')->add('dateTimeCreation')        ;
        //$builder->add('version')->add('codeIATA')->add('codeOACI')->add('capaciteSiege')->add('tempsDemiTour')        ;

        $builder
            ->add('version', TextType::class, array(
                'label' => 'Version',
                'attr' => array(
                    'placeholder'       => "Version"
                ),
                'required' => false
            ))
            ->add('codeIATA', TextType::class, array(
                'label' => 'Code IATA',
                'attr' => array(
                    'placeholder'       => "Saisir Code IATA",
                    'maxlength'         => 3
                ),
                'required' => false
            ))
            ->add('codeOACI', TextType::class, array(
                'label' => 'Code OACI',
                'attr' => array(
                    'placeholder'       => "Saisir Code OACI",
                    'maxlength'         => 4
                ),
                'required' => false
            ))
            ->add('capaciteSiege', IntegerType::class, array(
                'label' => 'Capacité siège',
                'attr' => array(
                    'placeholder'       => "Saisir Capacité siège"
                ),
                'required' => false
            ))
            ->add('tempsDemiTour', IntegerType::class, array(
                'label' => 'Temps demi tour',
                'attr' => array(
                    'placeholder'       => "Saisir Temps demi tour"
                ),
                'required' => false
            ))
        ;

        $builder->get('codeIATA')
            ->addModelTransformer(new CallbackTransformer(
                function ($codeIATA) {
                    return strtoupper($codeIATA);
                },
                function ($codeIATA) {
                    return strtoupper($codeIATA);
                }
            ));

        $builder->get('codeOACI')
            ->addModelTransformer(new CallbackTransformer(
                function ($codeOACI) {
                    return strtoupper($codeOACI);
                },
                function ($codeOACI) {
                    return strtoupper($codeOACI);
                }
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\TypeAvion'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_typeavion';
    }


}
