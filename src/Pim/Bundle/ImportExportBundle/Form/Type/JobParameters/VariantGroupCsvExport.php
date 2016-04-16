<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;

/**
 * FormsOptions for variant group CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupCsvExport implements FormsOptionsInterface
{
    /** @var SimpleCsvExport */
    protected $simpleCsvExport;

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
     * @param SimpleCsvExport            $simpleCsvExport
     * @param array                      $supportedJobNames
     * @param array                      $decimalSeparators
     * @param array                      $dateFormats
     */
    public function __construct(
        SimpleCsvExport $simpleCsvExport,
        array $supportedJobNames,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->simpleCsvExport   = $simpleCsvExport;
        $this->supportedJobNames = $supportedJobNames;
        $this->decimalSeparators = $decimalSeparators;
        $this->dateFormats       = $dateFormats;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        $formOptions = [
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
        $formOptions = array_merge($formOptions, $this->simpleCsvExport->getOptions());

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
