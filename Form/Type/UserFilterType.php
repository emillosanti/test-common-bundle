<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Class SearchContactType
 */
class UserFilterType extends AbstractType
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
        $builder->add('query', EntityType::class, [
            'placeholder' => 'Tous les utilisateurs',
            'class' => $this->entities['user']['class'],
            'attr' => ['autocomplete' => 'off'],
            'required' => false,
            'label' => 'Utilisateurs',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                  ->where('u.enabled = 1')
                  ->orderBy('u.firstName', 'ASC');
            },
            'data'  => $options['user-data']
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
            'user-data' => null,
        ]);
    }
}
