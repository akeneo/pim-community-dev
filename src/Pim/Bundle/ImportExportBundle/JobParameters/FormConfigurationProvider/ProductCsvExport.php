<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * FormsOptions for product CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvExport implements FormConfigurationProviderInterface
{
    /** @var FormConfigurationProviderInterface */
    protected $simpleCsvExport;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var array */
    protected $decimalSeparators;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var array */
    protected $dateFormats;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param FormConfigurationProviderInterface $simpleCsvExport
     * @param ChannelRepositoryInterface         $channelRepository
     * @param FamilyRepositoryInterface          $familyRepository
     * @param JobRepositoryInterface             $jobRepository
     * @param TranslatorInterface                $translator
     * @param PresenterInterface                 $datePresenter
     * @param LocaleResolver                     $localeResolver
     * @param array                              $supportedJobNames
     * @param array                              $decimalSeparators
     * @param array                              $dateFormats
     */
    public function __construct(
        FormConfigurationProviderInterface $simpleCsvExport,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        JobRepositoryInterface $jobRepository,
        TranslatorInterface $translator,
        PresenterInterface $datePresenter,
        LocaleResolver $localeResolver,
        array $supportedJobNames,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->simpleCsvExport   = $simpleCsvExport;
        $this->channelRepository = $channelRepository;
        $this->familyRepository  = $familyRepository;
        $this->jobRepository     = $jobRepository;
        $this->translator        = $translator;
        $this->datePresenter     = $datePresenter;
        $this->localeResolver    = $localeResolver;
        $this->supportedJobNames = $supportedJobNames;
        $this->decimalSeparators = $decimalSeparators;
        $this->dateFormats       = $dateFormats;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfiguration(JobInstance $jobInstance)
    {
        $formOptions = [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelRepository->getLabelsIndexedByCode(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.channel.label',
                    'help'     => 'pim_connector.export.channel.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
            'locales' => ['type' => 'pim_import_export_product_export_locale_choice'],
            'families' => [
                'type'    => 'change_family_type',
                'options' => [
                    'repository' => $this->familyRepository,
                    'route' => 'pim_enrich_family_rest_index',
                    'required' => false,
                    'multiple' => true,
                    'label' => 'pim_base_connector.export.families.label',
                    'help' => 'pim_base_connector.export.families.help',
                    'attr' => ['data-tab' => 'content', 'data-placeholder' => 'Choose a family']
                ]
            ],
            'enabled' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'enabled'  => 'pim_connector.export.status.choice.enabled',
                        'disabled' => 'pim_connector.export.status.choice.disabled',
                        'all'      => 'pim_connector.export.status.choice.all'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.status.label',
                    'help'     => 'pim_connector.export.status.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
            'updated' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'all'         => 'pim_connector.export.updated.choice.all',
                        'last_export' => 'pim_connector.export.updated.choice.last_export'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.updated.label',
                    'help'     => 'pim_connector.export.updated.help',
                    'info'     => $this->getLastExecution($jobInstance),
                    'attr'     => ['data-tab' => 'content']
                ],
            ],
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->decimalSeparators,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.decimalSeparator.label',
                    'help'     => 'pim_base_connector.export.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->dateFormats,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.dateFormat.label',
                    'help'     => 'pim_base_connector.export.dateFormat.help',
                ]
            ],
        ];
        $formOptions = array_merge($formOptions, $this->simpleCsvExport->getFormConfiguration($jobInstance));

        return $formOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }

    /**
     * Get last execution job
     *
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    protected function getLastExecution(JobInstance $jobInstance)
    {
        $lastExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);

        if (null === $lastExecution) {
            return $this->translator->trans('pim_connector.export.updated.last_execution.none');
        }

        return $this->translator->trans('pim_connector.export.updated.last_execution.last', [
            '%date%' => $this->datePresenter->present($lastExecution->getStartTime(), [
                'locale' => $this->localeResolver->getCurrentLocale()
            ])
        ]);
    }
}
