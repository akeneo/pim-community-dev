<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;

/**
 * FormsOptions for simple CSV import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleCsvImport implements FormConfigurationProviderInterface
{
    /** @var array */
    protected $supportedJobNames;

    /**
     * @param array $supportedJobNames
     */
    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfiguration()
    {
        return [
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.import.csv.filePath.label',
                    'help'  => 'pim_connector.import.csv.filePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.csv.uploadAllowed.label',
                    'help'  => 'pim_connector.import.csv.uploadAllowed.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_connector.import.csv.delimiter.label',
                    'help'  => 'pim_connector.import.csv.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_connector.import.csv.enclosure.label',
                    'help'  => 'pim_connector.import.csv.enclosure.help'
                ]
            ],
            'escape' => [
                'options' => [
                    'label' => 'pim_connector.import.csv.escape.label',
                    'help'  => 'pim_connector.import.csv.escape.help'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
