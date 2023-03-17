<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Migration;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
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
        $this->connection->transactional(function () {
            $limit = 1000;
            $sql = <<<SQL
               SELECT id
               FROM pim_catalog_category_translation
               WHERE label = ''
               LIMIT :limit; 
           SQL;

            while (true) {
                $rows = $this->connection->fetchAllAssociative($sql,
                    [
                        'limit' => $limit,
                    ],
                    [
                        'limit' => \PDO::PARAM_INT,
                    ]
                );
                if (empty($rows)) {
                    return;
                }
                $ids = array_map(fn ($row) => $row['id'], $rows);
                $this->updateCategoryTranslation($ids);
            }
        });
    }

    /**
     * @param array<int> $ids
     */
    private function updateCategoryTranslation(array $ids): void
    {
        $this->connection->executeQuery(<<<SQL
                UPDATE pim_catalog_category_translation
                SET label=NULL
                WHERE id IN (:ids);
            SQL,
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY],
        );
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
