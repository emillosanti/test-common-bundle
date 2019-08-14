<?php

namespace SAM\CommonBundle\Form\Type;

use SAM\CommonBundle\Entity\InteractionAppointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InteractionAppointmentType
 */
class InteractionAppointmentType extends AbstractType
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
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Objet de la réunion',
                'required' => false
            ])
            ->add('contacts', CardCollectionType::class, [
                'label' => 'Participants',
                'attr' => ['placeholder' => 'Rechercher un participant'],
                'entry_type' => ContactMergedCardChoiceType::class,
                'ajax_route' => 'search_contacts_merged',
                'required' => false
            ])
        ;
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
        $resolver->setDefault('data_class', $this->entities['interaction_appointment']['class']);
    }
}
