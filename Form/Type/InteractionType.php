<?php

namespace SAM\CommonBundle\Form\Type;

use SAM\CommonBundle\Entity\InteractionEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use AppBundle\Entity;

/**
 * Class InteractionType
 */
class InteractionType extends AbstractType
{
    private $reminderDelays;

    public function __construct($reminderDelays)
    {
        $this->reminderDelays = $reminderDelays;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = isset($options['data']) ? $options['data'] : null;

        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Contenu', 'rows' => 8]
            ])
            ->add('eventDate', DateType::class, [
                'label' => 'Date',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => [ 'data-provide' => 'datepicker' ]
            ])
            ->add('reminderDate', DateType::class, [
                'label' => 'Date de relance',
                'required' => false,
                'data' => ($data && $data->getReminderDate()) ? $data->getReminderDate() : new \DateTime(),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => [ 'data-provide' => 'datepicker' ]
            ]);

        $class = (new \ReflectionClass($builder->getData()))->getShortName();
        $delay = isset($this->reminderDelays[$class]) ? $this->reminderDelays[$class] : null;

        if ($delay && !$data->getReminderDate()) {
            $builder->get('reminderDate')->setData(new \DateTime("+ $delay days"));
        }
    }
}
