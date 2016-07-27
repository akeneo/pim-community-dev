<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;

/**
 * FormsOptions for variant group CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupCsvExport implements FormConfigurationProviderInterface
{
    /** @var FormConfigurationProviderInterface */
    protected $simpleOptions;

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
     * @param FormConfigurationProviderInterface      $simpleOptions
     * @param array                      $supportedJobNames
     * @param array                      $decimalSeparators
     * @param array                      $dateFormats
     */
    public function __construct(
        FormConfigurationProviderInterface $simpleOptions,
        array $supportedJobNames,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->simpleOptions     = $simpleOptions;
        $this->supportedJobNames = $supportedJobNames;
        $this->decimalSeparators = $decimalSeparators;
        $this->dateFormats       = $dateFormats;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfiguration()
    {
        $formOptions = [
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->decimalSeparators,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.decimalSeparator.label',
                    'help'     => 'pim_connector.export.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->dateFormats,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.dateFormat.label',
                    'help'     => 'pim_connector.export.dateFormat.help',
                ]
            ],
        ];
        $formOptions = array_merge($formOptions, $this->simpleOptions->getFormConfiguration());

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
