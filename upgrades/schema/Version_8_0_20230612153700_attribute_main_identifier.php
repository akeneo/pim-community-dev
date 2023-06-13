<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230612153700_attribute_main_identifier extends AbstractMigration
{
    public function getDescription(): string
    {
        return <<<EOL
            Due to a delay between the merges of the DB post install subscriber and the doctrine migration PRs,
            the main identifier may not be set on environments created between the merges.
            This re-runs the needed scripts on those environments.
            EOL;
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            !$schema->getTable('pim_catalog_attribute')->hasColumn('main_identifier'),
            'main_identifier column does not exist in pim_catalog_attribute'
        );
        if (!$this->hasMainIdentifier()) {
            $this->addSql(<<<SQL
                UPDATE pim_catalog_attribute pca
                INNER JOIN
                (
                    SELECT id
                    FROM pim_catalog_attribute
                    WHERE attribute_type = 'pim_catalog_identifier'
                    ORDER BY id ASC
                    LIMIT 1
                ) t ON pca.id = t.id
                SET main_identifier = true
                SQL
            );
        } else {
            $this->disableMigrationWarning();
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function hasMainIdentifier(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT * FROM pim_catalog_attribute
                WHERE main_identifier = true
            ) as is_existing
        SQL;

        return (bool) $this->connection->executeQuery($sql)->fetchOne();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
