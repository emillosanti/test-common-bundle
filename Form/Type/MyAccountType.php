<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MyAccountType
 */
class MyAccountType extends AbstractType
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
            ->add(
                'notifiedByEmail',
                CheckboxType::class,
                [
                    'label' => 'Notifications par e-mail pour les demandes d\'accès',
                    'required' => false
                ]
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->entities['user']['class'],
            ]
        );
    }
}
