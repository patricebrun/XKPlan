<?php

namespace AirCorsica\XKPlanBundle\Form;

use AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\ResetType;

class AvionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('nom')->add('affrete')->add('ordre')->add('dateTimeModification')->add('dateTimeCreation')->add('typeAvion')        ;
        //$builder->add('nom')->add('typeAvion')->add('compagnie')->add('affrete')->add('ordre')       ;

        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir Nom"
                ),
                'required' => false
            ))
            ->add('typeAvion', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:TypeAvion',
                'choice_label' => 'version',
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Selectionner un Type Avion"
                ),
                'required' => false
            ))
            ->add('compagnie', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:Compagnie',
                'choice_label' => 'nom',
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Selectionner une compagnie"
                ),
                'required' => false
            ))
            ->add('affrete', CheckboxType::class, array(
                'label' => 'Affrèttement',
                'required' => false,
            ))
            ->add('ordre', IntegerType::class, array(
                'label' => 'Ordre',
                'attr' => array(
                    'placeholder'       => "Saisir ordre liste"
                ),
                'required' => false,
                'empty_data' => '0'
            ))
            ->add('periodesImmobilsation', CollectionType::class, array(
                'entry_type' => PeriodeImmobilisationType::class,
                'allow_add'  => true,
                'by_reference' => false,
                'allow_delete' => true,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Avion',
            'allow_extra_fields' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_avion';
    }


}
