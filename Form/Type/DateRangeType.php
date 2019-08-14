<?php

namespace SAM\CommonBundle\Form\Type;

use Faker\Provider\DateTime;
use SAM\CommonBundle\Form\Model\DateRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DateRangeType
 */
class DateRangeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateType::class, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Date de début']
            ])
            ->add('end', DateType::class, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Date de fin'],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DateRange::class,
        ]);
    }

}
