<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;

/**
 * FormsOptions for simple YAML import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleYamlImport implements FormConfigurationProviderInterface
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
                    'label' => 'pim_connector.import.yml.filePath.label',
                    'help'  => 'pim_connector.import.yml.filePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.yml.uploadAllowed.label',
                    'help'  => 'pim_connector.import.yml.uploadAllowed.help'
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
