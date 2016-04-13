<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Component\Connector\Writer\File\CsvWriter;

/**
 * Class ConfigurationFieldsRegistry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParameterFieldsRegistry
{
    /**
     * @param JobInterface $job
     *
     * @return array
     */
    public function getFields(JobInterface $job)
    {
        // TODO: test!!
        if ('pim_base_connector.jobs.csv_attribute_export.title' === $job->getName()) {
            return [
                'filePath' => [
                    'options' => [
                        'label' => 'pim_connector.export.filePath.label',
                        'help'  => 'pim_connector.export.filePath.help'
                    ]
                ],
                'delimiter' => [
                    'options' => [
                        'label' => 'pim_connector.export.delimiter.label',
                        'help'  => 'pim_connector.export.delimiter.help'
                    ]
                ],
                'enclosure' => [
                    'options' => [
                        'label' => 'pim_connector.export.enclosure.label',
                        'help'  => 'pim_connector.export.enclosure.help'
                    ]
                ],
                'withHeader' => [
                    'type'    => 'switch',
                    'options' => [
                        'label' => 'pim_connector.export.withHeader.label',
                        'help'  => 'pim_connector.export.withHeader.help'
                    ]
                ],
            ];
        } else {
            return $this->getFieldsFromStepElements($job);
        }
    }

    /**
     * Ensure Backward Compatibility with PIM <= CE-1.5
     *
     * @param JobInterface $job
     *
     * @return array
     *
     * @deprecated will be removed in 1.7, please use a tagged service to define your configuration fields
     */
    private function getFieldsFromStepElements(JobInterface $job)
    {
        $configuration = [];
        if (method_exists($job, 'getSteps')) {
            foreach ($job->getSteps() as $step) {
                if (method_exists($step, 'getConfigurableStepElements')) {
                    foreach ($step->getConfigurableStepElements() as $stepElement) {
                        if (method_exists($stepElement, 'getConfigurationFields')) {
                            $configuration = array_merge($configuration, $stepElement->getConfigurationFields());
                        }
                    }
                }
            }
        }

        return $configuration;
    }
}
