<?php

namespace SAM\CommonBundle\Admin;

use SAM\CommonBundle\Entity\GenericDocumentCategory;
use SAM\InvestorBundle\Entity\InvestorDocumentCategory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumentCategoryAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_documentcategory';
    protected $baseRoutePattern = 'documentcategory';

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('enableSignatureDate', ChoiceType::class, [
                'label' => 'Enable Signature Date',
                'required' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
            ]);

        $subject = $this->getSubject();

        // Investor DocumentCategory specific fields
        if ($subject instanceof InvestorDocumentCategory) {
            // Use if needed
        }
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'Nom'])
            ->add('enableSignatureDate', null, ['label' => 'Signature Date'])
            ->add('position', null, ['label' => 'Position'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'move' => [
                        'template' => '@PixSortableBehavior/Default/_sort.html.twig'
                    ],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('documentCategoryType', 'doctrine_orm_callback', [
            'callback' => function ($queryBuilder, $alias, $field, $value) {
                if (!is_array($value) || !array_key_exists('value', $value) || empty($value['value'])) {
                    return false;
                }

                $queryBuilder->andWhere($alias . ' INSTANCE OF :documentCategoryType');
                $queryBuilder->setParameter('documentCategoryType', $value['value']);

                return true;
            },
        ],
            ChoiceType::class,
            [
                'choices' => array_flip([
                    $this->getClassDiscriminatorString(GenericDocumentCategory::class) => 'Generic',
                    $this->getClassDiscriminatorString(InvestorDocumentCategory::class) => 'Investor',
                ]),
                'translation_domain' => $this->getTranslationDomain(),
            ]);
    }

    /**
     * @param $class
     * @return string
     */
    private function getClassDiscriminatorString($class) {
        try {
            return (new \ReflectionClass($class))->getShortName();
        } catch (\ReflectionException $e) {
        }
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('move', $this->getRouterIdParameter() . '/move/{position}');
    }
}
