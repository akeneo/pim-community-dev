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
 * FormsOptions for product XLSX export
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXlsxExport implements FormConfigurationProviderInterface
{
    /** @var FormConfigurationProviderInterface */
    protected $simpleXlsxExport;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

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

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param FormConfigurationProviderInterface $simpleXlsxExport
     * @param ChannelRepositoryInterface         $channelRepository
     * @param FamilyRepositoryInterface          $familyRepository
     * @param array                              $supportedJobNames
     * @param array                              $decimalSeparators
     * @param array                              $dateFormats
     */
    public function __construct(
        FormConfigurationProviderInterface $simpleXlsxExport,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        array $supportedJobNames,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->simpleXlsxExport = $simpleXlsxExport;
        $this->channelRepository = $channelRepository;
        $this->supportedJobNames = $supportedJobNames;
        $this->decimalSeparators = $decimalSeparators;
        $this->dateFormats = $dateFormats;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfiguration(JobInstance $jobInstance)
    {
        $formOptions = [
            'filters' => [
                'type'    => 'hidden',
                'options' => [
                    'attr' => [
                        'data-tab' => 'content'
                    ]
                ]
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
            'linesPerFile' => [
                'type'    => 'integer',
                'options' => [
                    'label' => 'pim_connector.export.lines_per_files.label',
                    'help'  => 'pim_connector.export.lines_per_files.help',
                ]
            ],
        ];

        $formOptions = array_merge($formOptions, $this->simpleXlsxExport->getFormConfiguration($jobInstance));

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
