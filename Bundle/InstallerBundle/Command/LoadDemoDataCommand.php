<?php

namespace Oro\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

class LoadDemoDataCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:demo:fixtures:load')
            ->setDescription('Load demo data fixtures to your database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading demo data ...');
        $container = $this->getContainer();
        $loader    = new ContainerAwareLoader($container);
        foreach ($container->get('kernel')->getBundles() as $bundle) {
            if (is_dir($path = $bundle->getPath() . '/DataFixtures/Demo')) {
                $loader->loadFromDirectory($path);
            }
        }

        $executor = new ORMExecutor($container->get('doctrine.orm.entity_manager'));
        $executor->setLogger(
            function ($message) use ($output) {
                $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
            }
        );
        $executor->execute($loader->getFixtures(), true);
    }
}
