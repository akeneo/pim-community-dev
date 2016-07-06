<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

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
     * @param array                              $supportedJobNames
     * @param array                              $decimalSeparators
     * @param array                              $dateFormats
     */
    public function __construct(
        FormConfigurationProviderInterface $simpleCsvExport,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        array $supportedJobNames,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->simpleCsvExport = $simpleCsvExport;
        $this->familyRepository = $familyRepository;
        $this->channelRepository = $channelRepository;
        $this->supportedJobNames = $supportedJobNames;
        $this->decimalSeparators = $decimalSeparators;
        $this->dateFormats = $dateFormats;
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
            'locales'  => ['type' => 'pim_import_export_product_export_locale_choice'],
            'families' => [
                'type'    => 'select_family_type',
                'options' => [
                    'repository' => $this->familyRepository,
                    'route'      => 'pim_enrich_family_rest_index',
                    'required'   => false,
                    'multiple'   => true,
                    'label'      => 'pim_base_connector.export.families.label',
                    'help'       => 'pim_base_connector.export.families.help',
                    'attr'       => [
                        'data-tab'         => 'content',
                        'data-placeholder' => 'pim_base_connector.export.families.placeholder'
                    ]
                ]
            ],
            'product_identifier' => [
                'type'    => 'pim_product_identifier_choice',
                'options' => [
                    'multiple'    => true,
                    'label'       => 'pim_connector.export.product_identifier.label',
                    'help'        => 'pim_connector.export.product_identifier.help',
                    'placeholder' => 'pim_connector.export.product_identifier.placeholder',
                    'attr'        => [
                        'data-tab' => 'content',
                    ]
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
            'completeness' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'at_least_one_complete' => 'pim_connector.export.completeness.choice.at_least_one_complete',
                        'all_complete'          => 'pim_connector.export.completeness.choice.all_complete',
                        'all_incomplete'        => 'pim_connector.export.completeness.choice.all_incomplete',
                        'all'                   => 'pim_connector.export.completeness.choice.all'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.completeness.label',
                    'help'     => 'pim_connector.export.completeness.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
            'updated_since' => [
                'type'    => 'pim_updated_since_parameter_type',
                'options' => [
                    'job_instance' => $jobInstance,
                    'label'        => 'pim_connector.export.updated.updated_since_strategy.label',
                    'help'         => 'pim_connector.export.updated.updated_since_strategy.help',
                    'attr'         => ['data-tab' => 'content']
                ]
            ],
            'categories'       => ['type' => 'pim_import_export_product_export_categories'],
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
}
