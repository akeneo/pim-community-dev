<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\ImportExportBundle\Form\DataTransformer\ConfigurationToJobParametersTransformer;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\JobAliasSubscriber;
use Pim\Bundle\ImportExportBundle\Provider\JobLabelProvider;
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

    /** @var JobLabelProvider */
    protected $jobLabelProvider;

    /**
     * @param ConnectorRegistry   $connectorRegistry
     * @param TranslatorInterface $translator
     * @param JobLabelProvider    $jobLabelProvider
     */
    public function __construct(
        ConnectorRegistry $connectorRegistry,
        TranslatorInterface $translator,
        JobLabelProvider $jobLabelProvider
    ) {
        $this->connectorRegistry = $connectorRegistry;
        $this->translator        = $translator;
        $this->jobLabelProvider  = $jobLabelProvider;
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
     * @return JobInstanceType
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
     * @return JobInstanceType
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
     * @return JobInstanceType
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
     * @return JobInstanceType
     */
    protected function addAliasField(FormBuilderInterface $builder)
    {
        $choices = [];
        foreach ($this->connectorRegistry->getJobs($this->jobType) as $connector => $jobs) {
            foreach ($jobs as $key => $job) {
                $choices[$connector][$key] = $this->jobLabelProvider->getJobLabel($job->getName());
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
     * @return JobInstanceType
     */
    protected function addJobConfigurationField(FormBuilderInterface $builder)
    {
        // TODO: TIP-426: rename this field to parameters
        $builder
            ->add(
                'configuration',
                'pim_import_export_job_parameters',
                [
                    'required'      => true,
                    'by_reference'  => false,
                    'property_path' => 'rawConfiguration',
                ]
            )
            ->get('configuration')
            ->addModelTransformer(new ConfigurationToJobParametersTransformer());

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
     * @return JobInstanceType
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
