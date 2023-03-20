<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;

class CodeInterneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('libelle')->add('dateTimeModification')->add('dateTimeCreation')->add('createur')->add('modificateur')        ;

        $builder
            ->add('libelle', TextType::class, array(
                'label' => 'Libelle',
                'attr' => array(
                    'placeholder'       => "Saisir Libelle code"
                ),
                'required' => false
            ))
            //->add('save', SubmitType::class, array('label' => 'Enregistrer'))
            //->add('cancel', ResetType::class, array('label' => 'Reset'))
        ;

        $builder->get('libelle')
        ->addModelTransformer(new CallbackTransformer(
            function ($libelle) {
                return strtoupper($libelle);
            },
            function ($libelle) {
                return strtoupper($libelle);
            }
        ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\CodeInterne'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_codeinterne';
    }


}
