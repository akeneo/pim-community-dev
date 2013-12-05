<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Form type related to metric entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unitOptions['choices'] = array_combine(array_keys($options['units']), array_keys($options['units']));
        $unitOptions['select2'] = true;
        if ($options['default_unit']) {
            $unitOptions['preferred_choices'] = $options['default_unit'];
        }

        $builder
            ->add('id', 'hidden')
            ->add('data', 'number')
            ->add('unit', 'choice', $unitOptions)
            ->add('family', 'hidden', array('data' => $options['family']));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'   => 'Pim\Bundle\FlexibleEntityBundle\Entity\Metric',
                'units'        => array(),
                'default_unit' => null,
                'family'       => null
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_metric';
    }
}
