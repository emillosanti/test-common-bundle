<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CardCollectionType
 */
class CardCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => CardChoiceType::class,
            'entry_options' => [
                'choice_label' => 'fullName',
            ],
            'by_reference' => false,
            'ajax_params' => []
        ]);
        $resolver->setRequired(['ajax_route']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['ajax_route'] = $options['ajax_route'];
        if (array_key_exists('ajax_params', $options)) {
            $view->vars['ajax_params'] = $options['ajax_params'];
        }

        if ($options['autocomplete_preloaded']) {
            $preloadedOptions = [];

            foreach ($view->vars['prototype']->vars['choices'] as $choice) {
                $preloadedOptions[] = [
                    'id' => $choice->value,
                    'name' => $choice->label,
                    'text' => $choice->label,
                    'transform' => false,
                    'visible' => true,
                ];
            }

            $view->vars['preloaded_options'] = $preloadedOptions;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
