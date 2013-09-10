<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Form type related to metric entity
 */
class MetricType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unitOptions['choices'] = $options['units'];
        if ($options['default_unit']) {
            $unitOptions['preferred_choices'] = $options['default_unit'];
        }

        $builder
            ->add('id', 'hidden')
            ->add('data', 'number')
            ->add('unit', 'choice', $unitOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'   => 'Oro\Bundle\FlexibleEntityBundle\Entity\Metric',
                'units'        => array(),
                'default_unit' => null,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_metric';
    }
}
