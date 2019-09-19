<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Command;

use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh versioning data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshCommand extends Command
{
    protected static $defaultName = 'pim:versioning:refresh';

    /** @var LoggerInterface */
    private $logger;

    /** @var VersionManager */
    private $versionManager;

    /** @var BulkObjectDetacherInterface */
    private $bulkObjectDetacher;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        LoggerInterface $logger,
        VersionManager $versionManager,
        BulkObjectDetacherInterface $bulkObjectDetacher,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->versionManager = $versionManager;
        $this->bulkObjectDetacher = $bulkObjectDetacher;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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
            $this->logger->pushHandler(new StreamHandler('php://stdout'));
        }
        $totalPendings = (int) $this->versionManager
            ->getVersionRepository()
            ->getPendingVersionsCount();

        if ($totalPendings === 0) {
            $output->writeln('<info>Versioning is already up to date.</info>');

            return;
        }

        $progress = new ProgressBar($output, $totalPendings);
        $progress->start();

        $batchSize = $input->getOption('batch-size');

        $om = $this->getObjectManager();

        $pendingVersions = $this->versionManager
            ->getVersionRepository()
            ->getPendingVersions($batchSize);

        $nbPendings = count($pendingVersions);

        while ($nbPendings > 0) {
            $previousVersions = [];
            foreach ($pendingVersions as $pending) {
                $key = sprintf('%s_%s', $pending->getResourceName(), $pending->getResourceId());

                $previousVersion = isset($previousVersions[$key]) ? $previousVersions[$key] : null;
                $version = $this->createVersion($pending, $previousVersion);

                if ($version) {
                    $previousVersions[$key] = $version;
                }

                $progress->advance();
            }
            $om->flush();
            $this->bulkObjectDetacher->detachAll($pendingVersions);

            $pendingVersions = $this->versionManager
                ->getVersionRepository()
                ->getPendingVersions($batchSize);
            $nbPendings = count($pendingVersions);
        }
        $progress->finish();
        $output->writeln(sprintf('<info>%d created versions.</info>', $totalPendings));
    }

    /**
     * @param Version $version
     * @param Version $previousVersion
     *
     * @return Version|null
     */
    protected function createVersion(Version $version, Version $previousVersion = null)
    {
        $version = $this->versionManager->buildPendingVersion($version, $previousVersion);

        if ($version->getChangeset()) {
            $this->entityManager->persist($version);
            $this->entityManager->flush($version);

            return $version;
        } else {
            $this->entityManager->remove($version);
            $this->entityManager->flush($version);
        }
    }
}
