<?php
/**
 * Created by PhpStorm.
 * User: sfernandes
 * Date: 26/12/2016
 * Time: 17:13
 */
namespace AirCorsica\XKPlanBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorPickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(TextType::class));
    }
    public function getParent()
    {
        return TextType::class;
    }
    public function getBlockPrefix()
    {
        return 'color';
    }
}