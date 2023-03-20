<?php

namespace AirCorsica\XKPlanBundle\Form;

use AirCorsica\XKPlanBundle\Entity\CodeShareVol;
use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class VolType extends AbstractType
{

    /**
     * @var $em EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(array_key_exists('id_precedent',$options['attr'])){
            $id_precedent = $options['attr']['id_precedent'];
        }else{
            $id_precedent = '';
        }

        if(array_key_exists('dateDebut2',$options['attr'])){
            $datesDebut =  $options['attr']['dateDebut2'].'_'.$options['attr']['dateFin2'];
        }else{
            $datesDebut = '';
        }

        $builder
            ->add('id',HiddenType::class)
            ->add('id_precedent',HiddenType::class,array(
                'data' => $id_precedent,
                'mapped' => false,))

            ->add('dates2_precedent',HiddenType::class,array(
                'data' => $datesDebut,
                'mapped' => false,))

            ->add('ligne',HiddenType::class,array(
                'data' => ''
            ))
            ->add('numero',TextType::class,array(
                //'data' => $options['data']->getNumero() ? $options['data']->getNumero() : 'XK',
                'data' => $options['data']->getNumero()!='' ? $options['data']->getNumero() : 'XK',
                'attr' => array(
                   // 'tabindex' => '2'
                )
            ))
            //->add('commentaire')
            ->add('temps_demi_tour',TextType::class,array(
                'attr' => array(
                    'readonly' => 'readonly',
                ),
                'label_attr' => array(
                    'class' => '',
                ),
                'mapped' => false,
                'label' => 'Temps 1/2 tour'
            ))
            ->add('avion',EntityType::class,array(
                    'class' => 'AirCorsicaXKPlanBundle:Avion',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->orderBy('a.affrete', 'ASC')
                            ->addOrderBy("a.ordre", 'ASC')
                            ->addOrderBy("a.nom", 'ASC');
                    },
                    'data' => $options['data']->getAvion() ? $options['data']->getAvion() : $this->em->getReference('AirCorsicaXKPlanBundle:Avion',$options['attr']['idAvion']),
                    'attr' => array(
                        'class' => "select2-single",
                        'data-placeholder' => "Sélectionner un Avion"
                    ),
                    'choice_attr' => function($avion) {
                        return ['data-compagnie' => $avion->getCompagnie()->getId(),'data-demitour-value' => $avion->getTypeAvion()->getTempsDemiTour()];
                    })
            )
            ->add('compagnie',EntityType::class,array(
                    'class' => 'AirCorsicaXKPlanBundle:Compagnie',
                    'data' => $options['data']->getCompagnie() ? $options['data']->getCompagnie() : $this->em->getReference('AirCorsicaXKPlanBundle:Compagnie',1),
                    'attr' => array(
                        'class' => "select2-single",
                        'data-placeholder' => "Sélectionner une Compagnie"
                    ))
            )
            ->add('typeDeVol', EntityType::class,array(
                'class' => 'AirCorsicaXKPlanBundle:TypeDeVol',
                'data' => $options['data']->getTypeDeVol() ? $options['data']->getTypeDeVol() : $this->em->getReference('AirCorsicaXKPlanBundle:TypeDeVol',1),
                'attr' => array(
                    'class' => "select2-single",
                    'data-placeholder' => "Sélectionner un Type de Vol"
                ))
            )
            ->add('affretement', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:Affretement',
                'data' => $options['data']->getAffretement() ? $options['data']->getAffretement() : $this->em->getReference('AirCorsicaXKPlanBundle:Affretement',1),
                'attr' => array(
                    'class' => "select2-single",
                    'data-placeholder' => "Sélectionner un Affretement",
                ))
            )
            /*->add('naturesDeVol', EntityType::class, array(
                'class' => 'AirCorsicaXKPlanBundle:NatureDeVol',
                'multiple' => true,
                'data' => sizeof($options['data']->getNaturesDeVol()->toArray()) ? $options['data']->getNaturesDeVol() : array(),
                'attr' => array(
                    'class'       => "select2-multiple",
                    'multiple' => "multiple",
                    'data-placeholder' => "Sélectionner des Natures de Vol"
                ))
            )*/

            //->add('template')
            ->add('periodeDeVol',PeriodeDeVolType::class,array(
                'label' => 'Périodes de validité',
            ))
            ->add('periodeDeVol2',PeriodeDeVolType::class,array(
                'mapped' => false,
                'label' => false,
            ))
            ->add('periode2',CheckboxType::class,array(
                'mapped' => false,
                'label' => 'Période de validité 2',
                'required' => false,
                'data' => array_key_exists('periode_2',$options['attr']) ? $options['attr']['periode_2'] : false,
            ))
            ->add('codesShareVol', EntityType::class, array(
                        'class' => 'AirCorsicaXKPlanBundle:CodeShareVol',
                        'multiple' => true,
                        'mapped' => false,
                        'required' => false,
                        'choices' => array(),
                        'data' => array(),
                        'attr' => array(
                            'class'    => "select2-multiple-add",
                            'multiple' => "multiple",
                            'data-placeholder' => "Sélectionner Codes Shares"
                        ))
                )
            ->add('aeroport_depart', EntityType::class, array(
                    'class' => 'AirCorsicaXKPlanBundle:Aeroport',
                    'choice_label' => function ($aeroport) {
                        return $aeroport->getCodeIATA();
                    },
                    'choices' => $this->em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findAll(),
                    'mapped' => false,
                    'choice_attr' => function($val, $key, $index) {
                        return ['data-aeroport-depart' => $index];
                    },
                    'attr' => array(
                        'class' => "select2-single combine",
                        'data-placeholder' => "Départ",
                        'style' => 'width: 100px;float: left;margin-right: 22px;',
                        //'tabindex' => '0'
                    ))
            )
            ->add('aeroport_arrivee', EntityType::class, array(
                    'class' => 'AirCorsicaXKPlanBundle:Aeroport',
                    'choice_label' => function ($aeroport) {
                        return $aeroport->getCodeIATA();
                    },
                    'choices' => $this->em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findAll(),
                    'choice_attr' => function($val, $key, $index) {
                        return ['data-aeroport-arrivee' => $index];
                    },
                    'mapped' => false,
                    'attr' => array(
                        'class' => "select2-single combine",
                        'data-placeholder' => "Arrivée",
                        'style' => 'width: 100px;float: left;',
                       // 'tabindex' => '1'
                    ))
            );
            /*->add('ligne', EntityType::class,array(
                'class' => 'AirCorsicaXKPlanBundle:Ligne',
                'attr' => array(
                    'class' => "select2-single",
                    'data-placeholder' => "Sélectionner une Ligne"
                ),
            ))*/



        $builder->get('codesShareVol')->resetViewTransformers();
        $builder->get('numero')
            ->addModelTransformer(new CallbackTransformer(
                function ($numero) {
                    return strtoupper($numero);
                },
                function ($numero) {
                    return strtoupper($numero);
                }
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\Vol'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_vol';
    }
}
