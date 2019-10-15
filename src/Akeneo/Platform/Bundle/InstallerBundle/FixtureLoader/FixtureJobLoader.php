<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load in database the job instances that can be used to install the PIM, once install, these job instance can be
 * removed
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FixtureJobLoader
{
    /** @staticvar */
    const JOB_TYPE = 'fixtures';

    /** @var JobInstancesBuilder */
    private $jobInstancesBuilder;

    /** @var JobInstancesConfigurator */
    private $jobInstancesConfigurator;

    /** @var BulkSaverInterface */
    private $jobInstanceSaver;

    /** @var BulkRemoverInterface */
    private $jobInstanceRemover;

    /** @var ObjectRepository */
    private $jobInstanceRepository;

    public function __construct(
        JobInstancesBuilder $jobInstancesBuilder,
        JobInstancesConfigurator $jobInstancesConfigurator,
        BulkSaverInterface $jobInstanceSaver,
        BulkRemoverInterface $jobInstanceRemover,
        ObjectRepository $jobInstanceRepository
    ) {
        $this->jobInstancesBuilder = $jobInstancesBuilder;
        $this->jobInstancesConfigurator = $jobInstancesConfigurator;
        $this->jobInstanceSaver = $jobInstanceSaver;
        $this->jobInstanceRemover = $jobInstanceRemover;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * Load the fixture jobs in database
     *
     * @param array $replacePaths
     *
     * @throws \Exception
     */
    public function loadJobInstances(string $catalogPath, array $replacePaths = [])
    {
        $jobInstances = $this->jobInstancesBuilder->build();
        $configuredJobInstances = $this->configureJobInstances($catalogPath, $jobInstances, $replacePaths);
        $this->jobInstanceSaver->saveAll($configuredJobInstances, ['is_installation' => true]);
    }

    /**
     * Deletes all the fixtures job
     */
    public function deleteJobInstances()
    {
        $jobInstances = $this->jobInstanceRepository->findBy(['type' => static::JOB_TYPE]);
        $this->jobInstanceRemover->removeAll($jobInstances);
    }

    /**
     * Get the list of stored jobs
     *
     * @return JobInstance[]
     */
    public function getLoadedJobInstances()
    {
        $jobs = $this->jobInstanceRepository->findBy(['type' => self::JOB_TYPE]);

        return $jobs;
    }

    /**
     * @param JobInstance[] $jobInstances
     * @param array         $replacePaths
     * @throws \Exception
     * @return JobInstance[]
     */
    protected function configureJobInstances(string $catalogPath, array $jobInstances, array $replacePaths)
    {
        if (0 === count($replacePaths)) {
            return $this->jobInstancesConfigurator->configureJobInstancesWithInstallerData($catalogPath, $jobInstances);
        } else {
            return $this->jobInstancesConfigurator->configureJobInstancesWithReplacementPaths(
                $jobInstances,
                $replacePaths
            );
        }
    }
}
