<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Step element configuration form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepElementConfigurationType extends AbstractType
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
                $form   = $event->getForm();
                $stepElement = $event->getData();

                foreach ($stepElement->getConfigurationFields() as $field => $config) {

                    if (isset($config['system']) && true === $config['system']) {
                        continue;
                    }

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
                            'label'           => ucfirst($field),
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
                'data_class' => 'Akeneo\\Bundle\\BatchBundle\\Item\\AbstractConfigurableStepElement',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_step_element_configuration';
    }
}
