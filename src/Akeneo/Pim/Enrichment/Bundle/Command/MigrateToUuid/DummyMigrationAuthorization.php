<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyMigrationAuthorization implements MigrationAuthorization
{
    /**
     * {@inheritDoc}
     */
    public function isGranted(): bool
    {
        return true;
    }
}
