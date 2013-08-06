<?php

namespace Pim\Bundle\BatchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for step element configuration
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepElementConfigurationType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form   = $event->getForm();
                $stepElement = $event->getData();

                foreach ($stepElement->getConfigurationFields() as $field => $config) {
                    $config = array_merge(
                        array(
                            'type'    => 'text',
                            'options' => array(),
                        ),
                        $config
                    );
                    $options = array_merge(
                        array(
                            'auto_initialize' => false,
                            'required'        => false,
                            'label'           => sprintf(
                                'pim_batch.%s.%s.label',
                                $stepElement->getName(),
                                $field
                            ),
                            'attr'            => array(
                                'help' => sprintf(
                                    'pim_batch.%s.%s.help',
                                    $stepElement->getName(),
                                    $field
                                )
                            )
                        ),
                        $config['options']
                    );

                    $form->add($factory->createNamed($field, $config['type'], null, $options));
                }
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
                'data_class' => 'Pim\\Bundle\\ImportExportBundle\\AbstractConfigurableStepElement',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_batch_step_element_configuration';
    }
}
