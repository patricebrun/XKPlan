<?php
/**
 * Created by PhpStorm.
 * User: patriceb
 * Date: 15/02/2017
 * Time: 15:26
 */

namespace AirCorsica\XKPlanBundle\Form\Type;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatePickerType extends AbstractType
{
    /**
     * @var $em EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    public function getParent()
    {
        return DateType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_datepicker';
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        if(array_key_exists('data-saison',$options['attr']) && 'false' == $options['attr']['data-saison']){
            $view->vars['saisons'] = null;
        }else{
            $repoSaison = $this->em->getRepository('AirCorsicaXKPlanBundle:Saison');
            $aSaison = $repoSaison->findAll();
            $result = array();
            foreach ($aSaison as $saison){
                $result[$saison->getId()] = array(
                    'saison'=>$saison,
                    'periodes' => $saison->getPeriodesSaison('IATA'));
            }
            $view->vars['saisons'] = $result;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'extended_options'  =>  array()
        ));
    }

}