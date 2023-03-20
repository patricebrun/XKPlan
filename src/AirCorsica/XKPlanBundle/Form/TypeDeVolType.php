<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AirCorsica\XKPlanBundle\Form\Type\ColorPickerType;
use Symfony\Component\Form\CallbackTransformer;

class TypeDeVolType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('nom')->add('codeType')->add('codeService')->add('codeCouleur')->add('dateTimeModification')->add('dateTimeCreation')        ;
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir nom"
                ),
                'required' => false
            ))
            ->add('codeType', TextType::class, array(
                'label' => 'Code Type',
                'attr' => array(
                    'placeholder'       => "Saisir code type",
                    'maxlength'         => 3
                ),
                'required' => false
            ))
            ->add('codeService', TextType::class, array(
                'label' => 'Code Service',
                'attr' => array(
                    'placeholder'       => "Saisir code service",
                    'maxlength'         => 1
                ),
                'required' => false
            ))
            ->add('codeCouleur', ColorPickerType::class, array(
                'label' => 'Code couleur',
                'attr' => array(
                    'placeholder'   => "Selectionner une couleur",
                ),
                'required' => false
            ))
            //->add('save', SubmitType::class, array('label' => 'Enregistrer'))
            //->add('cancel', ResetType::class, array('label' => 'Reset'))
        ;

        $builder->get('codeType')
            ->addModelTransformer(new CallbackTransformer(
                function ($codeType) {
                    return strtoupper($codeType);
                },
                function ($codeType) {
                    return strtoupper($codeType);
                }
            ));

        $builder->get('codeService')
            ->addModelTransformer(new CallbackTransformer(
                function ($codeService) {
                    return strtoupper($codeService);
                },
                function ($codeService) {
                    return strtoupper($codeService);
                }
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\TypeDeVol'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_typedevol';
    }


}
