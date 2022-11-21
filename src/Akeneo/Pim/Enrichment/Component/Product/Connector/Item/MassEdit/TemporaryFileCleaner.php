<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * Temporary file cleaner. It cleans file after the mass edit is done because
 * we have 2 PHP processes, and once the first process is finished, temporary files
 * are deleted and we cannot retrieve uploaded files so we moved temporary files to the upload directory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemporaryFileCleaner implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @throws \InvalidArgumentException If 'actions' index is missing from $configuration
     */
    public function execute()
    {
        $actions = $this->getConfiguredActions();
        foreach ($actions as $action) {
            if (isset($action['value']['filePath']) && is_file($action['value']['filePath'])) {
                unlink($action['value']['filePath']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * @return array|null
     */
    protected function getConfiguredActions()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return $jobParameters->get('actions');
    }
}
