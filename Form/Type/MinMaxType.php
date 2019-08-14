<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MinMaxType
 */
class MinMaxType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', NumberType::class, [
                'scale' => 0,
                'data' => $options['defaultMin'],
                'required' => false,
            ])
            ->add('max', NumberType::class, [
                'scale' => 0,
                'data' => $options['defaultMax'],
                'required' => false,
            ])
            ->add('defaultMin', HiddenType::class, [
                'data' => $options['defaultMin'],
                'empty_data' => $options['defaultMin'],
                'required' => false,
            ])
            ->add('defaultMax', HiddenType::class, [
                'data' => $options['defaultMax'],
                'empty_data' => $options['defaultMax'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'defaultMin' => 0,
            'defaultMax' => 20,
        ]);
    }
}
