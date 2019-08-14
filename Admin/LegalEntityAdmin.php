<?php

namespace SAM\CommonBundle\Admin;

use SAM\CommonBundle\Entity\LegalEntity;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class LegalEntityAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_legalentity';
    protected $baseRoutePattern = 'legalentity';
    
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('investmentVehicule', CheckboxType::class, ['label' => 'Véhicule d\'investissement', 'required' => false])
            ->add('parentCompany', CheckboxType::class, ['label' => 'Société mère', 'required' => false])
            ->add('fundsRaised', NumberType::class, [ 'label' => 'Montant', 'attr' => [ 'step' => 0.01 ], 'required' => false,])
            ->add('parent', ModelListType::class)
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'Nom'])
            ->add('fundsRaised', null, ['label' => 'Montant'])
            ->add('investmentVehicule', null, ['label' => 'Véhicule d\'investissement'])
            ->add('parentCompany', null, ['label' => 'Société mère'])
            ->add('parent', null, array(
                'associated_property' => function(LegalEntity $legalEntity) {
                    return $legalEntity->getParent() ? $legalEntity->getParent() : $legalEntity->getName();
                },
                'sortable' => true,
                'sort_field_mapping' => array('fieldName' => 'name'),
                'sort_parent_association_mappings' => array(array('fieldName' => 'parent'))
            ))
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }
}
