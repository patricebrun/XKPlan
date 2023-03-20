<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ChangePasswordFormType';
    }

    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_utilisateur_changepassword';
    }
}