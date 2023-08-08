<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Pim\Enrichment\Bundle\Command\RestoreSortedAssetCollectionDueToIncidentCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Psr\Log\LoggerInterface;

/**
 * Incident PIM-11120
 *
 * See \Akeneo\Pim\Enrichment\Bundle\Command\RestoreSortedAssetCollectionDueToIncidentCommand
 * The command is idempotent. Product and product models already fixed will not be fixed again if the migration is re-excuted for any reason.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20230804100000RestoreSortedAssetsDueToIncidentZddMigrationV2 implements ZddMigration
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RestoreSortedAssetCollectionDueToIncidentCommand $command
    ) {
    }

    public function migrate(): void
    {
        $this->command->restoreAssets(withDryRun: false);

        $numberProductVersions = $this->command->getNumberProductVersions();
        $this->logger->notice(sprintf('PIM-11120: "%d/%d" products restored. The detail is in the table "%s".', $numberProductVersions['restored'], $numberProductVersions['affected'], RestoreSortedAssetCollectionDueToIncidentCommand::PRODUCT_TRACKING_TABLE_NAME));

        $numberProductModelVersions = $this->command->getNumberProductModelVersions();
        $this->logger->notice(sprintf('PIM-11120: "%d/%d" product models restored. The detail is in the table "%s".', $numberProductModelVersions['restored'], $numberProductModelVersions['affected'], RestoreSortedAssetCollectionDueToIncidentCommand::PRODUCT_MODEL_TRACKING_TABLE_NAME));
    }

    public function migrateNotZdd(): void
    {
        // no incident, so not needed
    }

    /**
     * Renamed as V2 to force its execution, as incident interval date was not the good one.
     * It means we missed some product or product models to restore during the execution of the V1 of the migration.
     */
    public function getName(): string
    {
        return 'RestoreSortedAssetsDueToIncidentV2';
    }
}
