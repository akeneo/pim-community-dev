<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\BatchBundle\Form\Type\AbstractConfigurationType;

/**
 * Export category type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportCategoryType extends AbstractConfigurationType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('content', 'integer', array('required' => true));
        $builder->add('locales', 'text', array('required' => true));
        $builder->add('tree', 'text', array('required' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ImportExportBundle\Configuration\ExportCategoryConfiguration'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_configuration_export_category';
    }
}
