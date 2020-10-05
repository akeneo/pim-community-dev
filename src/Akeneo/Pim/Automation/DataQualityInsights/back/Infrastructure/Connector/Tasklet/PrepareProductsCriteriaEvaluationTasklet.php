<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\MarkCriteriaToEvaluateInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PrepareEvaluationsParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class PrepareProductsCriteriaEvaluationTasklet implements TaskletInterface
{
    private const BULK_SIZE = 100;

    /** @var StepExecution */
    private $stepExecution;

    /** @var MarkCriteriaToEvaluateInterface */
    private $markProductCriteriaToEvaluate;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        MarkCriteriaToEvaluateInterface $markProductCriteriaToEvaluate,
        LoggerInterface $logger
    ) {
        $this->markProductCriteriaToEvaluate = $markProductCriteriaToEvaluate;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->createMissingCriteriaEvaluations();
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function createMissingCriteriaEvaluations(): void
    {
        try {
            $updatedSince = $this->updatedSince();
            $this->markProductCriteriaToEvaluate->forUpdatesSince($updatedSince, self::BULK_SIZE);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Failed to mark product criteria to evaluate',
                [
                    'error_code' => 'failed_to_mark_product_criteria_to_evaluate',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }

    private function updatedSince(): \DateTimeImmutable
    {
        $evaluateFrom = $this->stepExecution->getJobParameters()->get(PrepareEvaluationsParameters::UPDATED_SINCE_PARAMETER);

        return \DateTimeImmutable::createFromFormat(PrepareEvaluationsParameters::UPDATED_SINCE_DATE_FORMAT, $evaluateFrom);
    }
}
