<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributes;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

class EvaluateAttributesTasklet implements TaskletInterface
{
    /** @var EvaluateUpdatedAttributes */
    private $evaluateUpdatedAttributes;

    /** @var LoggerInterface */
    private $logger;

    /** @var StepExecution */
    private $stepExecution;

    public function __construct(EvaluateUpdatedAttributes $evaluateUpdatedAttributes, LoggerInterface $logger)
    {
        $this->evaluateUpdatedAttributes = $evaluateUpdatedAttributes;
        $this->logger = $logger;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        try {
            $this->evaluateUpdatedAttributes->evaluateAll();
        } catch (\Exception $exception) {
            if (null !== $this->stepExecution) {
                $this->stepExecution->addFailureException($exception);
            }
            $this->logger->error('The evaluations of the attributes has failed', [
                'error_code' => 'attributes_evaluation_failed',
                'error_message' => $exception->getMessage(),
                'step_execution_id' => $this->stepExecution->getId(),
            ]);
        }
    }
}
