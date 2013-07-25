<?php

namespace Pim\Bundle\BatchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepConfigurationType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
                $form      = $event->getForm();
                $step      = $event->getData();
                $reader    = $step->getReader();
                $processor = $step->getProcessor();
                $writer    = $step->getWriter();

                $form->add(
                    $factory->createNamed('reader', new StepElementConfigurationType(), $reader, array(
                        'label' => sprintf('Reader - %s', $reader->getName()),
                        'auto_initialize' => false,
                    ))
                );

                $form->add(
                    $factory->createNamed('processor', new StepElementConfigurationType(), $processor, array(
                        'label' => sprintf('Processor - %s', $processor->getName()),
                        'auto_initialize' => false,
                    ))
                );

                $form->add(
                    $factory->createNamed('writer', new StepElementConfigurationType(), $writer, array(
                        'label' => sprintf('Writer - %s', $writer->getName()),
                        'auto_initialize' => false,
                    ))
                );
            });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pim\\Bundle\\BatchBundle\\Step\\ItemStep',
        ));
    }


    public function getName()
    {
        return 'pim_batch_step_configuration';
    }
}
