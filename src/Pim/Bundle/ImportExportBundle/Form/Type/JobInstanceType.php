<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Oro\Bundle\BatchBundle\Form\Type\JobConfigurationType;
use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\RemoveDuplicateJobConfigurationSubscriber;

/**
 * Job instance form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', 'text')
            ->addEventSubscriber(new DisableFieldSubscriber('code'))
            ->add('label')
            ->add(
                'job',
                new JobConfigurationType(),
                array(
                    'required'     => false,
                    'by_reference' => false,
                )
            )
            ->get('job')
            ->addEventSubscriber(new RemoveDuplicateJobConfigurationSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_jobInstance';
    }
}
