<?php

namespace SAM\CommonBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class DocumentType
 */
class DocumentType extends AbstractType
{
    protected $entities;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * DocumentType constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct($entities, TokenStorageInterface $tokenStorage)
    {
        $this->entities = $entities;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'label' => 'Category',
                'class' => $this->entities['document_category']['class'],
                'constraints' => [new NotBlank()],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.position');
                }
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom du document',
                'constraints' => [new NotBlank()],
            ])
            ->add('url', UrlType::class, [
                'label' => 'Adresse du document',
                'constraints' => [new NotBlank()],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', $this->entities['document']['class']);
    }
}
