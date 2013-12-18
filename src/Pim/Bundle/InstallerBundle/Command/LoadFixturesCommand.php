<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pim\Bundle\InstallerBundle\Event\FixtureLoaderEvent;
use Pim\Bundle\InstallerBundle\FixtureLoader\Loader;

/**
 * Loads fixture files
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadFixturesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:load-fixtures')
            ->setDescription('Load fixture files.')
            ->addArgument('fixtures', InputArgument::IS_ARRAY|InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getEventDispatcher()->addListener(
            Loader::EVENT_STARTED,
            function (FixtureLoaderEvent $event) use ($output) {
                $output->write(sprintf('<info>Loading %s</info>', $event->getFile()));
            }
        );

        $this->getEventDispatcher()->addListener(
            Loader::EVENT_COMPLETED,
            function (FixtureLoaderEvent $event) use ($output) {
                $output->write(sprintf('<info>.</info>', $event->getFile()), true);
            }
        );
        $this->getLoader()->load(
            $this->getObjectManager(),
            $this->createReferenceRepository(),
            $input->getArgument('fixtures')
        );

        $output->writeln('<info>DONE</info>');
    }

    /**
     * @return ReferenceRepository
     */
    protected function createReferenceRepository()
    {
        $objectManager = $this->getObjectManager();
        $repository = new ReferenceRepository($this->getObjectManager());
        $listener = new ORMReferenceListener($repository);
        $objectManager->getEventManager()->addEventSubscriber($listener);

        return $repository;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    /**
     * Returns the fixture loader
     *
     * @return \Pim\Bundle\InstallerBundle\FixtureLoader\MultipleLoader
     */
    protected function getLoader()
    {
        return $this->getContainer()->get('pim_installer.fixture_loader.multiple_loader');
    }
}
