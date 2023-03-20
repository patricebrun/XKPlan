<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\ResetType;

class CompagnieType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('nom')->add('codeIATA')->add('codeAlternatif')->add('codeOACI')->add('dateTimeModification')->add('dateTimeCreation')        ;

        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir Nom"
                ),
                'required' => false
            ))
            ->add('codeIATA', TextType::class, array(
                'label' => 'Code IATA',
                'attr' => array(
                    'placeholder'       => "Saisir Code IATA",
                    'maxlength'         => 2
                ),
                'required' => false
            ))
            ->add('codeAlternatif', TextType::class, array(
                'label' => 'Code Alternatif',
                'attr' => array(
                    'placeholder'       => "Saisir Code Alternatif"
                ),
                'required' => false
            ))
            ->add('codeOACI', TextType::class, array(
                'label' => 'Code OACI',
                'attr' => array(
                    'placeholder'       => "Saisir Code OACI",
                    'maxlength'         => 3
                ),
                'required' => false
            ))
            //->add('save', SubmitType::class, array('label' => 'Enregistrer'))
            //->add('cancel', ResetType::class, array('label' => 'Reset'))
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
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Compagnie'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_compagnie';
    }


}
