<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Jobs;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Utility class used to get observe the job executions.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionObserver
{
    /** @var SaverInterface */
    private $jobSaver;

    /** @var EntityManager */
    private $entityManager;

    /** @var EntityRepository */
    private $jobInstanceRepository;

    /** @var EntityRepository */
    private $jobExecutionsRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param IdentifiableObjectRepositoryInterface $jobExecutionRepository
     * @param SaverInterface                        $jobSaver
     * @param EntityManagerInterface                $entityManager
     */
    public function __construct(
        EntityRepository $jobInstanceRepository,
        EntityRepository $jobExecutionRepository,
        SaverInterface $jobSaver,
        EntityManagerInterface $entityManager
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobExecutionsRepository = $jobExecutionRepository;
        $this->jobSaver = $jobSaver;
        $this->entityManager = $entityManager;
    }

    public function jobExecutions(): array
    {
        return $this->jobExecutionsRepository->findAll();
    }

    public function jobExecutionsWithJobName(string $jobName): array
    {
        $jobInstance = $this->jobInstanceRepository->findOneBy(['code' => $jobName]);
        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('No job instance found for job name "%s"', $jobName));
        }

        $jobExecutions = $jobInstance->getJobExecutions()->toArray();

        return $jobExecutions;
    }

    public function purge(string $jobName): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneBy(['code' => $jobName]);
        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('No job instance found for job name "%s"', $jobName));
        }

        $jobExecutions = $jobInstance->getJobExecutions();
        foreach ($jobExecutions as $jobExecution) {
            $jobInstance->removeJobExecution($jobExecution);
        }

        $this->jobSaver->save($jobInstance);
        $this->entityManager->clear();
    }
}
