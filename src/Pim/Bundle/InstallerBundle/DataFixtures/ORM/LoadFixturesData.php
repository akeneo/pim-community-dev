<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
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
class LoadFixturesData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getJobs($manager) as $job) {
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
     * Get the list of stored jobs
     *
     * @param ObjectManager $manager
     *
     * @return array
     */
    protected function getJobs(ObjectManager $manager)
    {
        //TODO: use repository with class name as parameters
        /** @var JobInstance[] $jobs */
        $jobs = $manager->getRepository('AkeneoBatchBundle:JobInstance')->findAll();

        foreach ($jobs as $key => $job) {
            // Do not load products and associations with the ORM fixtures when MongoDB support is activated
            if (PimCatalogExtension::DOCTRINE_MONGODB_ODM === $this->container->getParameter('pim_catalog.storage_driver') &&
                (false !== strpos($job->getCode(), 'fixtures_product') ||
                    false !== strpos($job->getCode(), 'fixtures_association'))
            ) {
                unset($jobs[$key]);
            }

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
}
