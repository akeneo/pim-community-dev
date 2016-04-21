<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
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

    /** @var FixturePathProvider */
    protected $pathProvider;

    /** @var JobInstancesBuilder */
    protected $jobInstancesBuilder;

    /** @var JobInstancesConfigurator */
    protected $jobInstancesConfigurator;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param FixturePathProvider      $pathProvider
     * @param JobInstancesBuilder      $jobInstancesBuilder
     * @param JobInstancesConfigurator $jobInstancesConfigurator
     * @param ContainerInterface       $container
     */
    public function __construct(
        FixturePathProvider $pathProvider,
        JobInstancesBuilder $jobInstancesBuilder,
        JobInstancesConfigurator $jobInstancesConfigurator,
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->pathProvider = $pathProvider;
        $this->jobInstancesBuilder = $jobInstancesBuilder;
        $this->jobInstancesConfigurator = $jobInstancesConfigurator;
    }

    /**
     * Load the fixture jobs in database
     *
     * @param array $replacePaths
     */
    public function loadJobInstances(array $replacePaths = [])
    {
        $jobInstances = $this->jobInstancesBuilder->build();
        $configuredJobInstances = $this->configureJobInstances($jobInstances, $replacePaths);
        $saver = $this->getJobInstanceSaver();
        $saver->saveAll($configuredJobInstances);
    }

    /**
     * Deletes all the fixtures job
     */
    public function deleteJobInstances()
    {
        $jobInstances = $this->getJobInstanceRepository()->findBy(['type' => static::JOB_TYPE]);
        $remover = $this->getJobInstanceRemover();
        $remover->removeAll($jobInstances);
    }

    /**
     * Get the list of stored jobs
     *
     * @return JobInstance[]
     */
    public function getLoadedJobInstances()
    {
        $jobs = $this->getJobInstanceRepository()->findBy(['type' => self::JOB_TYPE]);

        return $jobs;
    }

    /**
     * @param JobInstance[] $jobInstances
     * @param array         $replacePaths
     * @throws \Exception
     * @return JobInstance[]
     */
    protected function configureJobInstances(array $jobInstances, array $replacePaths)
    {
        if (0 === count($replacePaths)) {
            return $this->jobInstancesConfigurator->configureJobInstancesWithInstallerData($jobInstances);
        } else {
            return $this->jobInstancesConfigurator->configureJobInstancesWithReplacementPaths(
                $jobInstances,
                $replacePaths
            );
        }
    }

    /**
     * @return BulkSaverInterface
     */
    protected function getJobInstanceSaver()
    {
        return $this->container->get('akeneo_batch.saver.job_instance');
    }

    /**
     * @return BulkRemoverInterface
     */
    protected function getJobInstanceRemover()
    {
        return $this->container->get('akeneo_batch.remover.job_instance');
    }

    /**
     * @return ObjectRepository
     */
    protected function getJobInstanceRepository()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em->getRepository($this->container->getParameter('akeneo_batch.entity.job_instance.class'));
    }
}
