<?php

namespace AirCorsica\XKPlanBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AdresseSITAType extends AbstractType
{

    /**
     * @var $em EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('libelle', TextType::class, array(
                'label' => 'Libellé',
                'attr' => array(
                    'placeholder'       => "Saisir un Libellé"
                ),
                'required' => false
            ))
            ->add('adresseSITA', TextType::class, array(
                'label' => 'Adresse SITA',
                'attr' => array(
                    'placeholder'       => "Saisir une Adresse SITA si vous n'utiliser pas un Email SITA."
                ),
                'required' => false
            ))
            ->add('email', TextType::class, array(
                'label' => 'Email SITA',
                'attr' => array(
                    'placeholder'       => "Saisir un Email SITA si vous n'utiliser pas une Adresse SITA."
                ),
                'required' => false
            ))
            ->add('aeroportAttache',EntityType::class,array(
                'label' => 'Aéroport d\'attache',
                'class' => 'AirCorsica\XKPlanBundle\Entity\Aeroport',
                'attr'  =>array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir un aéroport"
                ),
                'required' => false
            ))
            ->add('paysCoordinateur',EntityType::class,array(
                'label' => 'Coordinateur d\'un pays',
                'class' => 'AirCorsica\XKPlanBundle\Entity\Pays',
                'attr'  =>array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir un pays"
                ),
                'required' => false
            ))
//            ->add('email', HiddenType::class, array(
//                'data' => 'abcd'
//            ))
            ->add('compagnieAttachee',EntityType::class,array(
                'label' => 'Compagnie attachée',
                'class' => 'AirCorsica\XKPlanBundle\Entity\Compagnie',
                'attr'  =>array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir une compagnie"
                ),
                'required' => false
            ))
            ->add('suiviDemandeSlot');

        if(array_key_exists('idGroupe',$options['attr'])){

            $id_groupe = $this->em->getReference('AirCorsicaXKPlanBundle:GroupeSITA',$options['attr']['idGroupe']);

            $builder->add('groupeSITA',EntityType::class,array(
                'label' => 'Groupe SITA',
                'class' => 'AirCorsica\XKPlanBundle\Entity\GroupeSITA',
                'data' => $id_groupe,
                'attr'  =>array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir un groupe"
                ),
                'required' => true
            ));

        }else{

            //$id_groupe = '';
            $builder->add('groupeSITA',EntityType::class,array(
                'label' => 'Groupe SITA',
                'class' => 'AirCorsica\XKPlanBundle\Entity\GroupeSITA',
                //'data' => $options['data']->getGroupeSITA() ? $options['data']->getGroupeSITA() : $this->em->getReference('AirCorsicaXKPlanBundle:GroupeSITA',$options['attr']['idGroupe']),
                'attr'  =>array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Saisir un groupe"
                ),
                'required' => false
            ));

        }


        $builder->get('adresseSITA')
            ->addModelTransformer(new CallbackTransformer(
                function ($adresseSITA) {
                    return strtoupper($adresseSITA);
                },
                function ($adresseSITA) {
                    return strtoupper($adresseSITA);
                }
            ));

        $builder->get('libelle')
            ->addModelTransformer(new CallbackTransformer(
                function ($libelle) {
                    return strtoupper($libelle);
                },
                function ($libelle) {
                    return strtoupper($libelle);
                }
            ));

        $builder->get('email')
            ->addModelTransformer(new CallbackTransformer(
                function ($email) {
                    return strtolower($email);
                },
                function ($email) {
                    return strtolower($email);
                }
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\AdresseSITA'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_adressesita';
    }


}
