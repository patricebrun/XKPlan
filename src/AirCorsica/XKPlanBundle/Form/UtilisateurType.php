<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'label' => 'Email',
                'attr' => array(
                    'placeholder'       => "Saisir Email"
                ))
            )
            ->add('username', TextType::class, array(
                'label' => 'Username',
                'attr' => array(
                    'placeholder'       => "Saisir Username"
                )
            ));

        $builder->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe doivent correspondre',
            'required' => false,
            'first_options'  => array('label' => 'Mot de passe'),
            'second_options' => array('label' => 'Répétez Mot de passe'),
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
                'label' => 'Prénom',
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

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Utilisateur'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_utilisateur';
    }
}
