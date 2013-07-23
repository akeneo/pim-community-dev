<?php

namespace Pim\Bundle\BatchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\BatchBundle\Entity\Connector;

/**
 * Base connector type
 *
 *
 */
class ConnectorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('description', 'text', array('required' => true));

        if (isset($options['data'])) {
            $connector = $options['data'];

            // choose connector type during creation
            if (!$connector->getServiceId()) {
                $serviceIds = $options['serviceIds'];
                $choices = array();
                foreach ($serviceIds as $service) {
                    $choices[$service]= $service.'.label';
                }
                $builder->add('service_id', 'choice', array('required' => true, 'choices' => $choices));

            } else {
                $builder->add('service_id', 'text', array('disabled' => true));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pim\Bundle\BatchBundle\Entity\Connector'));
        $resolver->setRequired(array('serviceIds'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_batch_connector';
    }
}
