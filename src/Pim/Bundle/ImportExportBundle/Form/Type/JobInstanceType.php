<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\ImportExportBundle\Form\DataTransformer\ConfigurationToJobParametersTransformer;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\JobInstanceSubscriber;
use Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider;
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
    /** @var JobRegistry $jobRegistry */
    protected $jobRegistry;

    /** @var string $jobType */
    protected $jobType;

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /** @var TranslatorInterface */
    protected $translator;

    /** @var TranslatedLabelProvider */
    protected $jobLabelProvider;

    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /**
     * @param JobRegistry             $jobRegistry
     * @param TranslatorInterface     $translator
     * @param TranslatedLabelProvider $jobLabelProvider
     * @param JobParametersFactory    $jobParametersFactory
     */
    public function __construct(
        JobRegistry $jobRegistry,
        TranslatorInterface $translator,
        TranslatedLabelProvider $jobLabelProvider,
        JobParametersFactory $jobParametersFactory
    ) {
        $this->jobRegistry          = $jobRegistry;
        $this->translator           = $translator;
        $this->jobLabelProvider     = $jobLabelProvider;
        $this->jobParametersFactory = $jobParametersFactory;
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
            ->addJobNameField($builder)
            ->addJobConfigurationField($builder);

        $builder->addEventSubscriber(new JobInstanceSubscriber());

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
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
     * Add job name field
     *
     * @param FormBuilderInterface $builder
     *
     * @return JobInstanceType
     */
    protected function addJobNameField(FormBuilderInterface $builder)
    {
        $choices = [];
        foreach ($this->jobRegistry->allByTypeGroupByConnector($this->jobType) as $connector => $jobs) {
            foreach ($jobs as $key => $job) {
                $choices[$connector][$key] = $this->jobLabelProvider->getJobLabel($job->getName());
            }
        }

        $builder
            ->add(
                'jobName',
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
        $jobName = $builder->getData()->getAlias();

        if (null !== $jobName) {
            $job = $this->jobRegistry->get($jobName);
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
                ->addModelTransformer(new ConfigurationToJobParametersTransformer(
                    $this->jobParametersFactory,
                    $job
                ));
        }

        return $this;
    }
}
