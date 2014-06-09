<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load fixtures data
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractLoadFixturesData extends AbstractFixture implements
    OrderedFixtureInterface,
    ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getLaunchableJobs($manager) as $job) {
            $this->launchJob($job);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 100;
    }

    /**
     * Get the list of jobs that should be launched to load the fixture
     *
     * @return JobInstance[]
     */
    protected function getLaunchableJobs()
    {
        $jobs = $this->getAllJobs();

        foreach ($jobs as $key => $job) {
            // Do not load job when fixtures file is not available
            if (!is_readable($job->getRawConfiguration()['filePath'])) {
                unset($jobs[$key]);
            }
        }

        return $jobs;
    }

    /**
     * Launch a job
     * TODO: refactor all this
     *
     * @param JobInstance $job
     */
    protected function launchJob(JobInstance $job)
    {
        $app = new Application($this->container->get('kernel'));

        $cmd = new BatchCommand();
        $cmd->setContainer($this->container);
        $cmd->setApplication($app);
        $cmd->run(
            new ArrayInput(array(
                'command'     => 'akeneo:batch:job',
                'code'        => $job->getCode(),
            )),
            new ConsoleOutput()
        );
    }

    /**
     * Get the list of stored jobs
     *
     * @return JobInstance[]
     */
    protected function getAllJobs()
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');

        return $manager
            ->getRepository($this->container->getParameter('pim_catalog.entity.job_instance.class'))
            ->findBy(array('type' => FixtureJobLoader::JOB_TYPE));
    }
}
