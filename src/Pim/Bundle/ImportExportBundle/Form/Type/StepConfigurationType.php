<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Step configuration form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

                /* -> in progress, make it dynamic
                foreach ($step->getConfigurableStepElements() as $property => $element) {
                    $form->add(
                        $factory->createNamed(
                            $property,
                            'pim_import_export_step_element_configuration',
                            $element,
                            array(
                                'label' => sprintf('oro_batch.%s.title', $element->getName()),
                                'auto_initialize' => false,
                            )
                        )
                    );
                }
                 */

                $reader    = $step->getReader();
                $processor = $step->getProcessor();
                $writer    = $step->getWriter();

                $form->add(
                    $factory->createNamed(
                        'reader',
                        'pim_import_export_step_element_configuration',
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
                        'pim_import_export_step_element_configuration',
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
                        'pim_import_export_step_element_configuration',
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
        return 'pim_import_export_step_configuration';
    }
}
