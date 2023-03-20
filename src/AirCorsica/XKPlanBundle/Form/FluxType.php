<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FluxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('nom')->add('pathSMB')->add('loginSMB')->add('passwordSMB')->add('emailCible')->add('fluxActif')->add('pathCopieLocale')->add('type')->add('dateTimeModification')->add('dateTimeCreation')->add('template')->add('modificateur')        ;
        $builder
            ->add('pathSMB', TextType::class)
            ->add('loginSMB', TextType::class)
            ->add('passwordSMB', PasswordType::class)
            ->add('emailCible', EmailType::class)
            ->add('fluxActif', CheckboxType::class, array(
                'label'    => 'OUI : Les messages SSIM7 seront transférés à la messagerie paramétrée.',
                'required' => false,
            ))
            ->add('pathCopieLocale', TextType::class);
           /* ->add('type',ChoiceType::class, array(
            'choices'  => array(
                'Rocade' => '0',
                'Altea' => '1',
            )));*/
            //->add('type');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Flux'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_flux';
    }


}
