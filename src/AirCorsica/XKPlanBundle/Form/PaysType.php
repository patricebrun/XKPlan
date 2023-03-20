<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PaysType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('libelle')->add('code')->add('dateTimeModification')->add('dateTimeCreation')        ;
        $builder
            ->add('libelle', TextType::class, array(
                'label' => 'Libelle',
                'attr' => array(
                    'placeholder'       => "Saisir Libelle Pays"
                ),
                'required' => false
            ))
            ->add('code', TextType::class, array(
                'label' => 'Code',
                'attr' => array(
                    'placeholder'       => "Saisir Code",
                    'maxlength'         => 2
                ),
                'required' => false
            ))
        ;

        $builder->get('code')
            ->addModelTransformer(new CallbackTransformer(
                function ($code) {
                    return strtoupper($code);
                },
                function ($code) {
                    return strtoupper($code);
                }
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Pays'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_pays';
    }


}
