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

use Akeneo\Pim\Automation\DataQualityInsights\Application\PurgeOutdatedData;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PeriodicTasksParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class PurgeOutdatedDataTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var LoggerInterface */
    private $logger;

    /** @var PurgeOutdatedData */
    private $purgeOutdatedData;

    public function __construct(PurgeOutdatedData $purgeOutdatedData, LoggerInterface $logger)
    {
        $this->purgeOutdatedData = $purgeOutdatedData;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $jobParameters = $this->stepExecution->getJobParameters();
            $purgeDate = \DateTimeImmutable::createFromFormat(PeriodicTasksParameters::DATE_FORMAT, $jobParameters->get(PeriodicTasksParameters::DATE_FIELD));

            $this->purgeOutdatedData->purgeOutdatedCriterionEvaluations();
            $this->purgeOutdatedData->purgeProductAxisRatesFrom($purgeDate);
            $this->purgeOutdatedData->purgeDashboardProjectionRatesFrom($purgeDate);
        } catch (\Exception $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('Purge Data-Quality-Insights outdated data failed.', [
                'step_execution_id' => $this->stepExecution->getId(),
                'message' => $exception->getMessage()
            ]);
        }
    }
}
