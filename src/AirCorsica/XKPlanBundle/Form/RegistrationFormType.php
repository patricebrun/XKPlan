<?php

// src/AppBundle/Form/RegistrationFormType.php

namespace AirCorsica\XKPlanBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RegistrationFormType extends AbstractType

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

        $builder->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe doivent correspondre',
            'required' => false,
            'first_options'  => array(
                'label' => 'Mot de passe',
                'attr' => array(
                        'placeholder'       => "Saisir Mot de passe"
                )),
            'second_options' => array(
                'label' => 'Répétez Mot de passe',
                'attr' => array(
                    'placeholder'       => "Saisir Mot de passe"
                ))
        ));

        $builder->add('roles',ChoiceType::class,array(
            'label' => 'Choisissez un rôle',
//            'class' => 'AirCorsica\XKPlanBundle\Entity\Role',
            'choices' => array('Lecture' => 'ROLE_AUTEUR','Lecture / Ecriture' => 'ROLE_ADMIN'),
            'multiple' => true,
            'attr' => array(
                'class'       => "choixRoleUser"
            ),
            'expanded' => true,
            'required' => true
        ))
        ->add('nom', TextType::class, array(
            'label' => 'Nom',
            'attr' => array(
                'placeholder'       => "Saisir Nom"
            ),
            'required'    => false
        ))
        ->add('prenom', TextType::class, array(
            'label' => 'Prenom',
            'attr' => array(
                'placeholder'       => "Saisir Prénom"
            ),
            'required'    => false
        ))
        ->add('societe', TextType::class, array(
            'label' => 'Société',
            'attr' => array(
                'placeholder'       => "Saisir Société"
            ),
            'required'    => false
        ))
        ->add('note', TextareaType::class, array(
            'label' => 'Note',
            'attr' => array(
                'placeholder'       => "Saisir Note"
            ),
            'required'    => false
        ));
    }

    public function getParent()

    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()

    {
        return 'aircorsica_xkplanbundle_utilisateur_registration';
    }

}