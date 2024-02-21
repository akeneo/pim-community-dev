<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Job;

use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Refresh versioning data
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshVersioning implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private LoggerInterface $logger,
        private VersionManager $versionManager,
        private BulkObjectDetacherInterface $bulkObjectDetacher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function execute(): void
    {
        $batchSize = $this->stepExecution->getJobParameters()->get('batch_size');

        $totalPendings = (int)$this->versionManager
            ->getVersionRepository()
            ->getPendingVersionsCount();

        if ($totalPendings === 0) {
            $this->logger->info('<info>Versioning is already up to date.</info>');

            return;
        }

        $pendingVersions = $this->versionManager
            ->getVersionRepository()
            ->getPendingVersions($batchSize);

        $nbPendings = \count($pendingVersions);

        while ($nbPendings > 0) {
            $previousVersions = [];
            foreach ($pendingVersions as $pending) {
                $key = sprintf(
                    '%s_%s',
                    $pending->getResourceName(),
                    $pending->getResourceId() ?? $pending->getResourceUuid()->toString()
                );

                $previousVersion = $previousVersions[$key] ?? null;
                $version = $this->createVersion($pending, $previousVersion);

                if ($version) {
                    $previousVersions[$key] = $version;
                }
            }
            $this->entityManager->flush();
            $this->bulkObjectDetacher->detachAll($pendingVersions);

            $pendingVersions = $this->versionManager
                ->getVersionRepository()
                ->getPendingVersions($batchSize);
            $nbPendings = count($pendingVersions);
        }
        $this->logger->info(sprintf('<info>%d created versions.</info>', $totalPendings));
    }

    protected function createVersion(Version $version, Version $previousVersion = null): ?Version
    {
        $version = $this->versionManager->buildPendingVersion($version, $previousVersion);

        if ($version->getChangeset()) {
            $this->entityManager->persist($version);
            $this->entityManager->flush($version);

            return $version;
        }

        $this->entityManager->remove($version);
        $this->entityManager->flush($version);

        return null;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
