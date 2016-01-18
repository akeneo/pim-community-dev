<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\JobAliasSubscriber;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\RemoveDuplicateJobConfigurationSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Job instance form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceType extends AbstractType
{
    /** @var ConnectorRegistry $connectorRegistry */
    protected $connectorRegistry;

    /** @var string $jobType */
    protected $jobType;

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Constructor
     *
     * @param ConnectorRegistry   $connectorRegistry
     * @param TranslatorInterface $translator
     */
    public function __construct(ConnectorRegistry $connectorRegistry, TranslatorInterface $translator)
    {
        $this->connectorRegistry = $connectorRegistry;
        $this->translator        = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addCodeField($builder)
            ->addLabelField($builder)
            ->addConnectorField($builder)
            ->addAliasField($builder)
            ->addJobConfigurationField($builder);

        $builder->addEventSubscriber(new JobAliasSubscriber());

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * Add code field and subscriber
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addCodeField(FormBuilderInterface $builder)
    {
        $builder
            ->add('code', 'text')
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        return $this;
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add('label');

        return $this;
    }

    /**
     * Add connector field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addConnectorField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'connector',
                'hidden',
                [
                    'required'     => true,
                    'by_reference' => false,
                    'mapped'       => false
                ]
            );

        return $this;
    }

    /**
     * Add alias field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addAliasField(FormBuilderInterface $builder)
    {
        $choices = [];
        foreach ($this->connectorRegistry->getJobs($this->jobType) as $connector => $jobs) {
            if ('oro_importexport' !== $connector) {
                foreach ($jobs as $key => $job) {
                    $choices[$connector][$key] = $job->getName();
                }
            }
        }

        $builder
            ->add(
                'alias',
                'choice',
                [
                    'choices'      => $choices,
                    'required'     => true,
                    'by_reference' => false,
                    'mapped'       => false,
                    'empty_value'  => $this->translator->trans('pim_import_export.list'),
                    'empty_data'   => null,
                    'label'        => 'Job'
                ]
            );

        return $this;
    }

    /**
     * Add job configuration form type
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addJobConfigurationField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'job',
                'pim_import_export_job_configuration',
                [
                    'required'     => false,
                    'by_reference' => false,
                ]
            )
            ->get('job')
            ->addEventSubscriber(new RemoveDuplicateJobConfigurationSubscriber());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_jobInstance';
    }

    /**
     * Setter for job type
     *
     * @param string $jobType
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    public function setJobType($jobType)
    {
        $this->jobType = $jobType;

        return $this;
    }

    /**
     * Add an event subscriber
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }
}
