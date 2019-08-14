<?php

namespace SAM\CommonBundle\Form\Type;

use SAM\CommonBundle\Entity\Sourcing;
use SAM\CommonBundle\Entity\SourcingCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use SAM\AddressBookBundle\Form\DataTransformer\CompanyToIntTransformer;
use SAM\AddressBookBundle\Form\DataTransformer\ContactMergedToIntTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as EmailFieldType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SourcingType
 */
class SourcingType extends AbstractType
{
    protected $entities;

    /**
     * @var ContactMergedToIntTransformer
     */
    protected $contactTransformer;

    /**
     * @var CompanyToIntTransformer
     */
    protected $companyTransformer;

    public function __construct($entities, ContactMergedToIntTransformer $contactTransformer, CompanyToIntTransformer $companyTransformer)
    {
        $this->entities = $entities;
        $this->contactTransformer = $contactTransformer;
        $this->companyTransformer = $companyTransformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => $this->entities[$options['sourcing_category_class']]['class'],
                'placeholder' => 'Veuillez choisir une catégorie',
            ])
            ->add('contact', HiddenType::class, [
                'label' => 'Contact',
            ])
            ->add('company', HiddenType::class, [
                'label' => 'Société',
            ])
        ;

        $builder->get('contact')->addModelTransformer($this->contactTransformer);
        $builder->get('company')->addModelTransformer($this->companyTransformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entities['sourcing']['class'],
            'sourcing_category_class' => 'dealflow_sourcing_category'
        ]);
    }
}
