<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20220729171405CleanVersioningResourceUuidColumnForNonProductVersions implements ZddMigration
{
    public const BLOCKING_TRIGGER_NAME = 'pim_catalog_product_unique_data_uuid_insert';

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function migrate(): void
    {
        if ($this->migrationToRemoveTriggersHasNotRun()) {
            throw new \LogicException(
                'Migration Version_7_0_20220728121643_remove_uuid_triggers has to run before executing this ZDD migration.'
            );
        }
        $this->cleanVersioningResourceUuid();
    }

    public function getName(): string
    {
        return 'CleanVersioningResourceUuidColumnForNonProductVersions';
    }

    private function migrationToRemoveTriggersHasNotRun(): bool
    {
        $sql = <<<SQL
SELECT 1 from information_schema.triggers WHERE TRIGGER_NAME = 'pim_catalog_product_unique_data_uuid_insert';
SQL;
        return (bool) $this->connection->fetchOne($sql);
    }

    private function cleanVersioningResourceUuid(): void
    {
        $resourceNameToProcess = $this->findNextResourceNameToProcess();
        $totalVersionsCleaned = 0;
        while (null !== $resourceNameToProcess) {
            $versionIds = $this->getVersionIdsToCleanForResourceName($resourceNameToProcess);
            $this->cleanVersions($versionIds);
            $totalVersionsCleaned += \count($versionIds);
            $this->logger->notice(
                sprintf(
                    'Cleaned %d versions from their resource_uuid for resource name %s',
                    $totalVersionsCleaned,
                    $resourceNameToProcess
                )
            );
            $resourceNameToProcess = $this->findNextResourceNameToProcess();
        }
        $this->logger->notice(sprintf('Successfully cleaned a total of %d versions', $totalVersionsCleaned));
    }

    private function findNextResourceNameToProcess(): ?string
    {
        // This query takes ~40 secs to execute on a big catalog (4M versions)
        $sql = <<<SQL
SELECT DISTINCT resource_name
FROM pim_versioning_version
WHERE resource_uuid IS NOT NULL
;
SQL;

        $allResourceNames = $this->connection->fetchFirstColumn($sql);
        $nextResourceNameToProcess = current(array_filter(
            $allResourceNames,
            static fn(string $resourceName) => $resourceName !== Product::class
        ));

        return false !== $nextResourceNameToProcess ? $nextResourceNameToProcess : null;
    }

    private function cleanVersions(array $versionIdsToClean): void
    {
        $emptyResourceUuidQuery = <<<SQL
UPDATE pim_versioning_version SET resource_uuid = NULL, resource_name = resource_name WHERE id IN (:version_ids);
SQL;
        $this->connection->executeStatement(
            $emptyResourceUuidQuery,
            ['version_ids' => $versionIdsToClean],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function getVersionIdsToCleanForResourceName(string $resourceName): array
    {
        $sql = <<<SQL
SELECT id
FROM pim_versioning_version
WHERE
	resource_uuid IS NOT NULL
	AND resource_name = :resource_name
LIMIT 1000;
SQL;

        return $this->connection->fetchFirstColumn(
            $sql,
            ['resource_name' => $resourceName]
        );
    }
}
