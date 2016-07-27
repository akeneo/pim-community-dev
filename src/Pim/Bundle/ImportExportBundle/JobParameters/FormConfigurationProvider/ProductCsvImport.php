<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;

/**
 * FormsOptions for product CSV import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvImport implements FormConfigurationProviderInterface
{
    /** @var FormConfigurationProviderInterface */
    protected $simpleCsvImport;

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
     * @param FormConfigurationProviderInterface $simpleCsvImport
     * @param array                 $supportedJobNames
     * @param array                 $decimalSeparators
     * @param array                 $dateFormats
     */
    public function __construct(
        FormConfigurationProviderInterface $simpleCsvImport,
        array $supportedJobNames,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->simpleCsvImport   = $simpleCsvImport;
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
            'enabled' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.enabled.label',
                    'help'  => 'pim_connector.import.enabled.help'
                ]
            ],
            'categoriesColumn' => [
                'options' => [
                    'label' => 'pim_connector.import.categoriesColumn.label',
                    'help'  => 'pim_connector.import.categoriesColumn.help'
                ]
            ],
            'familyColumn' => [
                'options' => [
                    'label' => 'pim_connector.import.familyColumn.label',
                    'help'  => 'pim_connector.import.familyColumn.help'
                ]
            ],
            'groupsColumn' => [
                'options' => [
                    'label' => 'pim_connector.import.groupsColumn.label',
                    'help'  => 'pim_connector.import.groupsColumn.help'
                ]
            ],
            'enabledComparison' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.enabledComparison.label',
                    'help'  => 'pim_connector.import.enabledComparison.help'
                ]
            ],
            'realTimeVersioning' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.realTimeVersioning.label',
                    'help'  => 'pim_connector.import.realTimeVersioning.help'
                ]
            ]
        ];
        $formOptions = array_merge($this->simpleCsvImport->getFormConfiguration(), $formOptions);

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
