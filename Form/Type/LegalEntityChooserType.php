<?php

namespace SAM\CommonBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SAM\CommonBundle\Form\Model\LegalEntityModel;

/**
 * Class LegalEntityChooserType
 */
class LegalEntityChooserType extends AbstractType
{
    protected $entities;

    /**
     * LegalEntityChooserType constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('legalEntity', EntityType::class, [
                'label' => false,
                'class' => $this->entities['legal_entity']['class'],
                'attr' => [ 'class' => 'legal-entities-select' ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', LegalEntityModel::class);
    }
}
