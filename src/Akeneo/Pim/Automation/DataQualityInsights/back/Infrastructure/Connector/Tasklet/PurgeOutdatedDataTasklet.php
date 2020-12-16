<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\PurgeOutdatedData;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PeriodicTasksParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
