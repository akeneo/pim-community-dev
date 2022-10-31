<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\RecomputeProductScoresParameters;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\User;

final class RecomputeProductScoresTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    private const TIMEBOX_IN_SECONDS_ALLOWED = 900; // 15 minutes
    private const BULK_SIZE = 1000;

    public function __construct(
        private ConsolidateProductScores $consolidateProductScores,
        private Connection $connection,
        private JobLauncherInterface $queueJobLauncher,
        private JobInstanceRepository $jobInstanceRepository,
        private LoggerInterface $logger,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public function execute(): void
    {
        $startTime = time();
        $lastProductId = 0;

        try {
            $jobParameters = $this->stepExecution->getJobParameters();
            $lastProductId = $jobParameters->get(RecomputeProductScoresParameters::LAST_PRODUCT_ID);

            do {
                $uuids = $this->getNextProductUuids($lastProductId);
                if (empty($uuids)) {
                    return;
                }

                $this->consolidateProductScores->consolidate(
                    $this->idFactory->createCollection(array_map(fn ($productId) => (string) $productId, $uuids))
                );
                $lastProductId = end($uuids);
            } while (false === $this->isTimeboxReached($startTime));
        } catch (\Exception $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('Compute products scores failed', [
                'step_execution_id' => $this->stepExecution->getId(),
                'last_product_id' => $lastProductId,
                'message' => $exception->getMessage(),
            ]);
        }

        $this->scheduleNextRecomputeProductsScoresJob($lastProductId);
    }

    /**
     * @return string[]
     */
    private function getNextProductUuids($lastProductId): array
    {
        $stmt = $this->connection->executeQuery(
            sprintf(
                'SELECT BIN_TO_UUID(uuid) as uuid FROM pim_catalog_product WHERE id > %d ORDER BY id LIMIT %d',
                $lastProductId,
                self::BULK_SIZE
            )
        );

        return array_map(function ($resultRow) {
            return (string) $resultRow['uuid'];
        }, $stmt->fetchAllAssociative());
    }

    private function isTimeboxReached(int $startTime): bool
    {
        $actualTime = time();
        $timeSpentFromBegining = $actualTime - $startTime;

        if ($timeSpentFromBegining >= self::TIMEBOX_IN_SECONDS_ALLOWED) {
            return true;
        }

        return false;
    }

    private function scheduleNextRecomputeProductsScoresJob($lastProductId): void
    {
        $jobInstance = $this->getJobInstance();
        $user = new User(UserInterface::SYSTEM_USER_NAME, null);
        $jobParameters = [RecomputeProductScoresParameters::LAST_PRODUCT_ID => $lastProductId];
        $this->queueJobLauncher->launch($jobInstance, $user, $jobParameters);
    }

    private function getJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('data_quality_insights_recompute_products_scores');
        if (!$jobInstance instanceof JobInstance) {
            throw new \RuntimeException('The job instance "data_quality_insights_recompute_products_scores" does not exist. Please contact your administrator.');
        }

        return $jobInstance;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
