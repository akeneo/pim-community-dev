<?php

namespace Pim\Bundle\BatchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
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

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
                $form   = $event->getForm();
                $reader = $event->getData();

                foreach ($reader->getConfigurationFields() as $field => $config) {
                    $type    = isset($config['type']) ? $config['type'] : 'text';
                    $options = array_merge(array(
                        'auto_initialize' => false,
                        'required'        => false,
                    ), $config['options']);

                    $form->add($factory->createNamed($field, $type, null, $options));
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pim\\Bundle\\ImportExportBundle\\AbstractConfigurableStepElement',
        ));
    }

    public function getName()
    {
        return 'pim_batch_reader_configuration';
    }
}


