<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Migration;

use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20230313SetEmptyCategoryLabelsToNullZddMigration implements ZddMigration
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $this->connection->executeQuery(<<<SQL
            UPDATE pim_catalog_category_translation
            SET label=NULL
            WHERE label = '';
        SQL);
    }

    public function migrateNotZdd(): void
    {
        // Do nothing
    }

    public function getName(): string
    {
        return 'SetEmptyCategoryLabelsToNull';
    }
}
