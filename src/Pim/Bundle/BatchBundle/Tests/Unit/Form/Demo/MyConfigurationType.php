<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Form\Demo;

use Pim\Bundle\BatchBundle\Form\Type\AbstractConfigurationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Configuration form type
 */
class MyConfigurationType extends AbstractConfigurationType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('charset', 'text', array('required' => true));
        $builder->add('delimiter', 'text', array('required' => true));
        $builder->add('enclosure', 'text', array('required' => true));
        $builder->add('escape', 'text', array('required' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'my_configuration';
    }
}
