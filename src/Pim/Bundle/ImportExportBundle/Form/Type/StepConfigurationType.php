<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Form type for step configuration
 *
 */
class StepConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form      = $event->getForm();
                $step      = $event->getData();
                $reader    = $step->getReader();
                $processor = $step->getProcessor();
                $writer    = $step->getWriter();

                $form->add(
                    $factory->createNamed(
                        'reader',
                        new StepElementConfigurationType(),
                        $reader,
                        array(
                            'label' => sprintf('oro_batch.%s.title', $reader->getName()),
                            'auto_initialize' => false,
                        )
                    )
                );

                $form->add(
                    $factory->createNamed(
                        'processor',
                        new StepElementConfigurationType(),
                        $processor,
                        array(
                            'label' => sprintf('oro_batch.%s.title', $processor->getName()),
                            'auto_initialize' => false,
                        )
                    )
                );

                $form->add(
                    $factory->createNamed(
                        'writer',
                        new StepElementConfigurationType(),
                        $writer,
                        array(
                            'label' => sprintf('oro_batch.%s.title', $writer->getName()),
                            'auto_initialize' => false,
                        )
                    )
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\\Bundle\\BatchBundle\\Step\\ItemStep',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_batch_step_configuration';
    }
}
