<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'label' => 'Email',
                'attr' => array(
                    'placeholder'       => "Saisir Email"
                ),
                'required' => false,
            ))
            ->add('username', TextType::class, array(
                'label' => 'Username',
                'attr' => array(
                    'placeholder'       => "Saisir Username"
                ),
                'required' => false,
            ));

        $builder->add('nom', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder'       => "Saisir Nom"
                ),
                'required' => false
            ))
            ->add('prenom', TextType::class, array(
                'label' => 'Prénom',
                'attr' => array(
                    'placeholder'       => "Saisir Prénom"
                ),
                'required' => false
            ))
            ->add('societe', TextType::class, array(
                'label' => 'Société',
                'attr' => array(
                    'placeholder'       => "Saisir Société"
                ),
                'required' => false
            ))
            ->add('note', TextareaType::class, array(
                'label' => 'Note',
                'attr' => array(
                    'placeholder'       => "Saisir Note"
                ),
                'required' => false
            ));

    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_utilisateur_profile';
    }
}