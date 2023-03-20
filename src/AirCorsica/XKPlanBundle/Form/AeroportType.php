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
use Symfony\Component\Form\CallbackTransformer;

class AeroportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('nom')->add('codeIATA')->add('tempsDemiTour')->add('terminal')->add('coordonne')->add('notes')->add('dateTimeModification')->add('dateTimeCreation')->add('pays')        ;
        //$builder->add('nom')->add('codeIATA')->add('tempsDemiTour')->add('terminal')->add('pays')->add('coordonne')->add('notes')        ;

        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir Nom Aéroport"
                ),
                'required' => false
            ))
            ->add('codeIATA', TextType::class, array(
                'label' => 'Code IATA',
                'attr' => array(
                    'placeholder'       => "Saisir Code IATA",
                    'maxlength'         => 3
                ),'required' => false
            ))
            ->add('tempsDemiTour', IntegerType::class, array(
                'label' => 'Temps demi tour',
                'attr' => array(
                    'placeholder'       => "Saisir Temps demi tour"
                ),
                'required' => false
            ))
            ->add('terminal', TextType::class, array(
                'label' => 'Terminal',
                'attr' => array(
                    'placeholder'       => "Saisir Terminal"
                ),
                'required' => false,
            ))
            ->add('pays', EntityType::class, array(
                // query choices from this entity
                'class' => 'AirCorsicaXKPlanBundle:Pays',
                // use the User.username property as the visible option string
                'choice_label' => 'libelle',
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir un Pays"
                ),
                'required' => false
            ))
            ->add('coordonne', CheckboxType::class, array(
                'label' => 'Coordonné',
                'required' => false
            ))
            ->add('notes', TextareaType::class, array(
                'label' => 'Notes',
                'attr' => array(
                    'placeholder'       => "Saisir Notes"
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
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Aeroport'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_aeroport';
    }


}
