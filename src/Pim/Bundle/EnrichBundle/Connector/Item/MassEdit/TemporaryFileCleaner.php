<?php

namespace Pim\Bundle\EnrichBundle\Connector\Item\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Temporary file cleaner. It cleans file after the mass edit is done because
 * we have 2 PHP processes, and once the first process is finished, temporary files
 * are deleted and we cannot retrieve uploaded files so we moved temporary files to the upload directory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemporaryFileCleaner extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param array $configuration
     *
     * @throws \InvalidArgumentException If 'actions' index is missing from $configuration
     */
    public function execute(array $configuration)
    {
        if (!array_key_exists('actions', $configuration)) {
            throw new \InvalidArgumentException('Missing configuration \'actions\'.');
        }

        $actions = $configuration['actions'];

        foreach ($actions as $action) {
            if (isset($action['value']['filePath']) && is_file($action['value']['filePath'])) {
                unlink($action['value']['filePath']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }
}
