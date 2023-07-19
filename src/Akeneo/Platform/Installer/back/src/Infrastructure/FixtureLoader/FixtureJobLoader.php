<?php

namespace Akeneo\Platform\Installer\Infrastructure\FixtureLoader;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Load in database the job instances that can be used to install the PIM, once install, these job instance can be
 * removed.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FixtureJobLoader
{
    /** @staticvar */
    public const JOB_TYPE = 'fixtures';

    /** @var JobInstancesBuilder */
    private $jobInstancesBuilder;

    /** @var JobInstancesConfigurator */
    private $jobInstancesConfigurator;

    /** @var BulkSaverInterface */
    private $jobInstanceSaver;

    /** @var BulkRemoverInterface */
    private $jobInstanceRemover;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    public function __construct(
        JobInstancesBuilder $jobInstancesBuilder,
        JobInstancesConfigurator $jobInstancesConfigurator,
        BulkSaverInterface $jobInstanceSaver,
        BulkRemoverInterface $jobInstanceRemover,
        JobInstanceRepository $jobInstanceRepository,
    ) {
        $this->jobInstancesBuilder = $jobInstancesBuilder;
        $this->jobInstancesConfigurator = $jobInstancesConfigurator;
        $this->jobInstanceSaver = $jobInstanceSaver;
        $this->jobInstanceRemover = $jobInstanceRemover;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * Load the fixture jobs in database.
     *
     * @param array<string> $replacePaths
     *
     * @throws \Exception
     */
    public function loadJobInstances(string $catalogPath, array $replacePaths = []): void
    {
        $jobInstances = $this->jobInstancesBuilder->build();
        $configuredJobInstances = $this->configureJobInstances($catalogPath, $jobInstances, $replacePaths);
        $this->jobInstanceSaver->saveAll($configuredJobInstances, ['is_installation' => true]);
    }

    /**
     * Deletes all the fixtures job.
     */
    public function deleteJobInstances(): void
    {
        $jobInstances = $this->jobInstanceRepository->findBy(['type' => static::JOB_TYPE]);
        $this->jobInstanceRemover->removeAll($jobInstances);
    }

    /**
     * Get the list of stored jobs.
     *
     * @return JobInstance[]
     */
    public function getLoadedJobInstances(): array
    {
        $jobs = $this->jobInstanceRepository->findBy(['type' => self::JOB_TYPE]);

        return $jobs;
    }

    /**
     * @param JobInstance[] $jobInstances
     * @param array<string> $replacePaths
     *
     * @return JobInstance[]
     *
     * @throws \Exception
     */
    protected function configureJobInstances(string $catalogPath, array $jobInstances, array $replacePaths): array
    {
        if (0 === count($replacePaths)) {
            return $this->jobInstancesConfigurator->configureJobInstancesWithInstallerData($catalogPath, $jobInstances);
        } else {
            return $this->jobInstancesConfigurator->configureJobInstancesWithReplacementPaths(
                $jobInstances,
                $replacePaths,
            );
        }
    }
}
