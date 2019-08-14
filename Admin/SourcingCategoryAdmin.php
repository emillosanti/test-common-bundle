<?php

namespace SAM\CommonBundle\Admin;

use SAM\DealFlowBundle\Entity\DealFlowSourcingCategory;
use SAM\InvestorBundle\Entity\InvestorSourcingCategory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SourcingCategoryAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_sourcing_category';
    protected $baseRoutePattern = 'sourcing-category';

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', TextType::class, ['label' => 'Nom'])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('sourcingCategoryType', 'doctrine_orm_callback', [
            'callback' => function ($queryBuilder, $alias, $field, $value) {
                if (!is_array($value) || !array_key_exists('value', $value) || empty($value['value'])) {
                    return false;
                }

                $queryBuilder->andWhere($alias . ' INSTANCE OF :sourcingCategoryType');
                $queryBuilder->setParameter('sourcingCategoryType', $value['value']);

                return true;
            },
        ],
            ChoiceType::class,
            [
                'choices' => array_flip([
                    $this->getClassDiscriminatorString(DealFlowSourcingCategory::class) => 'Dealflow',
                    $this->getClassDiscriminatorString(InvestorSourcingCategory::class) => 'Investor',
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

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'Nom'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }
}
