<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\JobInstanceSubscriber;
use Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Job instance form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceFormType extends AbstractType
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

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param JobRegistry             $jobRegistry
     * @param TranslatorInterface     $translator
     * @param TranslatedLabelProvider $jobLabelProvider
     * @param JobParametersFactory    $jobParametersFactory
     * @param SecurityFacade          $securityFacade
     */
    public function __construct(
        JobRegistry $jobRegistry,
        TranslatorInterface $translator,
        TranslatedLabelProvider $jobLabelProvider,
        JobParametersFactory $jobParametersFactory,
        SecurityFacade $securityFacade
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->translator = $translator;
        $this->jobLabelProvider = $jobLabelProvider;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->securityFacade = $securityFacade;
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
            ->addJobNameField($builder);

        $builder->addEventSubscriber(new JobInstanceSubscriber());

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_import_export_jobInstance';
    }

    /**
     * Setter for job type
     *
     * @param string $jobType
     *
     * @return JobInstanceFormType
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $validationGroups = ['Default'];

        if ($this->securityFacade->isGranted('pim_importexport_export_profile_property_edit')) {
            $validationGroups = array_merge($validationGroups, ['FileConfiguration']);
        }

        if ($this->securityFacade->isGranted('pim_importexport_export_profile_content_edit')) {
            $validationGroups = array_merge($validationGroups, ['DataFilters']);
        }

        $resolver->setDefaults([
            'validation_groups' => $validationGroups
        ]);
    }

    /**
     * Add code field and subscriber
     *
     * @param FormBuilderInterface $builder
     *
     * @return JobInstanceFormType
     */
    protected function addCodeField(FormBuilderInterface $builder)
    {
        $builder
            ->add('code', TextType::class)
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        return $this;
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     *
     * @return JobInstanceFormType
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
     * @return JobInstanceFormType
     */
    protected function addConnectorField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'connector',
                HiddenType::class,
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
     * @return JobInstanceFormType
     */
    protected function addJobNameField(FormBuilderInterface $builder)
    {
        $choices = [];
        foreach ($this->jobRegistry->allByTypeGroupByConnector($this->jobType) as $connector => $jobs) {
            foreach ($jobs as $key => $job) {
                $choices[$connector][$this->jobLabelProvider->getJobLabel($job->getName())] = $key;
            }
        }

        $builder
            ->add(
                'jobName',
                ChoiceType::class,
                [
                    'choices'      => $choices,
                    'required'     => true,
                    'by_reference' => false,
                    'mapped'       => false,
                    'placeholder'  => $this->translator->trans('pim_import_export.list'),
                    'empty_data'   => null,
                    'label'        => 'Job'
                ]
            );

        return $this;
    }
}
