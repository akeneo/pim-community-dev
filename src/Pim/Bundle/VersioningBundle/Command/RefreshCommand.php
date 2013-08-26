<?php

namespace Pim\Bundle\VersioningBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Monolog\Handler\StreamHandler;
use Pim\Bundle\VersioningBundle\Entity\Pending;

/**
 * Refresh versioning data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:versioning:refresh')
            ->setDescription('Version any updated entities')
            ->addOption(
                'show-log',
                null,
                InputOption::VALUE_OPTIONAL,
                'display the log on the output'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDebug = $input->getOption('no-debug');

        if (!$noDebug) {
            $logger = $this->getContainer()->get('logger');
            // Fixme: Use ConsoleHandler available on next Symfony version (2.4 ?)
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $versionBuilder = $this->getContainer()->get('pim_versioning.manager.version_builder');
        $pendingVersions = $this->getEntityManager()->getRepository('PimVersioningBundle:Pending')
            ->findAll(array('status' => Pending::STATUS_PENDING));
        $nbPending = count($pendingVersions);

        if ($nbPending === 0) {
            $output->writeln(sprintf('<info>Versioning is already up to date.</info>'));

        } else {
            $progress = $this->getHelperSet()->get('progress');
            $ind = 0;
            $batchSize = 100;
            $progress->start($output, $nbPending);
            foreach ($pendingVersions as $pending) {

                $user = $this->getEntityManager()->getRepository('OroUserBundle:User')
                    ->findOneBy(array('username' => $pending->getUsername()));
                $repo = $this->getEntityManager()->getRepository($pending->getResourceName());
                $versionable = $repo->find($pending->getResourceId());
                $version = $versionBuilder->buildVersion($versionable, $user);
                //$version = $versionBuilder->buildVersion($versionable); TODO : audit

                $this->getEntityManager()->persist($version);
                $this->getEntityManager()->remove($pending);

                $ind++;

                if (($ind % $batchSize) == 0) {
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->clear('Pim\\Bundle\\VersioningBundle\\Entity\\Version');
                    $output->writeln(sprintf('<info>%d versions created.</info>', $batchSize));
                }

                $progress->advance();
            }
            $progress->finish();

            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getEntityManager();
    }
}
