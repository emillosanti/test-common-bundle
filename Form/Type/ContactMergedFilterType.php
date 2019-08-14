<?php

namespace SAM\CommonBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactMergedFilterType
 */
class ContactMergedFilterType extends AbstractType
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
        $contactMergedList = $options['contact_merged_list'];

        $builder->add('query', EntityType::class, [
            'placeholder' => 'Tous les contact',
            'query_builder' => function (EntityRepository $er) use ($contactMergedList) {
                return $er->createQueryBuilder('c')
                    ->where('c.id IN (:contact_merged_list)')
                    ->setParameter('contact_merged_list', $contactMergedList);
            },
            'class' => $this->entities['contact_merged']['class'],
            'attr' => ['autocomplete' => 'off'],
            'required' => false,
            'label' => 'Contact',
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'contact_merged_list' => [],
        ]);
    }
}
