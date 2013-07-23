<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Pim\Bundle\BatchBundle\Form\Type\AbstractConfigurationType;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Csv connector type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CsvConnectorType extends AbstractConfigurationType
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
            array(
                'data_class' => 'Pim\Bundle\ImportExportBundle\Configuration\CsvConfiguration'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_configuration_csv';
    }
}
