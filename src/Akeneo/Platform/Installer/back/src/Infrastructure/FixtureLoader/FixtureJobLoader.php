<?php

namespace Akeneo\Platform\Installer\Infrastructure\FixtureLoader;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Persistence\ObjectRepository;

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
    final public const JOB_TYPE = 'fixtures';

    /**
     * @param ObjectRepository<JobInstance> $jobInstanceRepository
     */
    public function __construct(private readonly JobInstancesBuilder $jobInstancesBuilder, private readonly JobInstancesConfigurator $jobInstancesConfigurator, private readonly BulkSaverInterface $jobInstanceSaver, private readonly BulkRemoverInterface $jobInstanceRemover, private readonly ObjectRepository $jobInstanceRepository)
    {
    }

    /**
     * Load the fixture jobs in database.
     *
     * @param array<string, array<string>> $replacePaths
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
    public function getLoadedJobInstances()
    {
        return $this->jobInstanceRepository->findBy(['type' => self::JOB_TYPE]);
    }

    /**
     * @param JobInstance[] $jobInstances
     * @param array<string, array<string>> $replacePaths
     *
     * @return JobInstance[]
     *
     * @throws \Exception
     */
    protected function configureJobInstances(string $catalogPath, array $jobInstances, array $replacePaths)
    {
        if ([] === $replacePaths) {
            return $this->jobInstancesConfigurator->configureJobInstancesWithInstallerData($catalogPath, $jobInstances);
        } else {
            return $this->jobInstancesConfigurator->configureJobInstancesWithReplacementPaths(
                $jobInstances,
                $replacePaths,
            );
        }
    }
}
