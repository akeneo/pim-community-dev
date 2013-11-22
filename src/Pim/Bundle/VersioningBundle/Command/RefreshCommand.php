<?php

namespace Pim\Bundle\VersioningBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Monolog\Handler\StreamHandler;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\VersioningBundle\Manager\PendingManager;

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
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'flush new versions by using this batch size',
                100
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
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $em = $this->getEntityManager();
        $pendingVersions = $this->getPendingManager()->getAllPendingVersions();
        $nbPendings = count($pendingVersions);
        if ($nbPendings === 0) {
            $output->writeln(sprintf('<info>Versioning is already up to date.</info>'));

        } else {
            $progress = $this->getHelperSet()->get('progress');
            $ind = 0;
            $batchSize = $input->getOption('batch-size');
            $progress->start($output, $nbPendings);
            $versioned = array();
            foreach ($pendingVersions as $pending) {
                $user = $em->getRepository('OroUserBundle:User')
                    ->findOneBy(array('username' => $pending->getUsername()));
                $versionable = $this->getPendingManager()->getRelatedVersionable($pending);
                if (!in_array(spl_object_hash($versionable), $versioned)) {
                    $this->getAddVersionListener()->createVersionAndAudit($em, $versionable, $user);
                    $versioned[] = spl_object_hash($versionable);
                }
                $em->remove($pending);
                $ind++;
                if (($ind % $batchSize) == 0) {
                    $em->flush();
                    $em->clear('Pim\\Bundle\\VersioningBundle\\Entity\\Version');
                }
                $progress->advance();
            }
            $progress->finish();
            $output->writeln(sprintf('<info>%d created versions.</info>', $nbPendings));
            $em->flush();
        }
    }

    /**
     * @return PendingManager
     */
    protected function getPendingManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.pending');
    }

    /**
     * @return AddVersionListener
     */
    protected function getAddVersionListener()
    {
        return $this->getContainer()->get('pim_versioning.event_listener.addversion');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
