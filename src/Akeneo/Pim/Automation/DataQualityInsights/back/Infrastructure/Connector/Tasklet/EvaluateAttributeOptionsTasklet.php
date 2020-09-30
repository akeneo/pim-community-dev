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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributeOptions;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\Evaluation;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class EvaluateAttributeOptionsTasklet implements TaskletInterface
{
    /** @var EvaluateUpdatedAttributeOptions */
    private $evaluateUpdatedAttributeOptions;

    /** @var LoggerInterface */
    private $logger;

    /** @var StepExecution */
    private $stepExecution;

    public function __construct(EvaluateUpdatedAttributeOptions $evaluateUpdatedAttributeOptions, LoggerInterface $logger)
    {
        $this->evaluateUpdatedAttributeOptions = $evaluateUpdatedAttributeOptions;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $this->evaluateUpdatedAttributeOptions->evaluateSince($this->evaluatedSince());
        } catch (\Exception $exception) {
            null !== $this->stepExecution && $this->stepExecution->addFailureException($exception);
            $this->logger->error('The evaluations of the attribute options has failed', [
                'error_code' => 'attribute_options_evaluation_failed',
                'error_message' => $exception->getMessage(),
                'step_execution_id' => $this->stepExecution->getId(),
            ]);
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function evaluatedSince(): \DateTimeImmutable
    {
        $evaluatedSince = $this->stepExecution->getJobParameters()->get(Evaluation::EVALUATED_SINCE_PARAMETER);

        return \DateTimeImmutable::createFromFormat(Evaluation::EVALUATED_SINCE_DATE_FORMAT, $evaluatedSince);
    }
}
