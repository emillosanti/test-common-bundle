<?php

namespace SAM\CommonBundle\Form\Type;

use SAM\CommonBundle\Entity\InteractionNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InteractionNoteType
 */
class InteractionNoteType extends AbstractType
{
    protected $entities;

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
        $builder->remove('eventDate');
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return InteractionType::class;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', $this->entities['interaction_note']['class']);
    }
}
