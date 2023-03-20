<?php

namespace AirCorsica\XKPlanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CodeSharePrechargeType extends AbstractType
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
        //$builder->add('dateTimeModification')->add('dateTimeCreation')->add('codeInterne')->add('createur')->add('modificateur')->add('libelle')        ;
        $builder
            ->add('libelle', TextType::class, array(
                'label' => 'Libelle',
                'attr' => array(
                    'placeholder'       => "Saisir libelle code"
                ),
                'required' => false
            ))
            ->add('codeInterne', EntityType::class, array(
                // query choices from this entity
                'class' => 'AirCorsicaXKPlanBundle:CodeInterne',
                // use the User.username property as the visible option string
                'choice_label' => 'libelle',
                'data' => $options['data']->getCodeInterne() ? $options['data']->getCodeInterne() : $this->em->getReference('AirCorsicaXKPlanBundle:CodeInterne',$options['attr']['idCodeInterne']),
                'attr' => array(
                    'class'       => "select2-single",
                    'data-placeholder' => "Selectionner un Code Interne"
                ),
                'required' => false
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ))
        ;

        $builder->get('libelle')
            ->addModelTransformer(new CallbackTransformer(
                function ($libelle) {
                    return strtoupper($libelle);
                },
                function ($libelle) {
                    return strtoupper($libelle);
                }
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aircorsica_xkplanbundle_codeshareprecharge';
    }


}
