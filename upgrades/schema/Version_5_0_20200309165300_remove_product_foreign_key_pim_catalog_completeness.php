<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200309165300_remove_product_foreign_key_pim_catalog_completeness extends AbstractMigration
{
    private function getForeignKeyIds(): array
    {
        $sql = <<<SQL
SELECT
  fk.id
FROM information_schema.INNODB_FOREIGN AS fk
  JOIN information_schema.INNODB_FOREIGN_COLS AS fk_cols
    ON fk.id = fk_cols.id
WHERE
  fk.for_name = '{db_name}/pim_catalog_completeness'
  AND fk.ref_name = '{db_name}/pim_catalog_product'
  AND fk_cols.for_col_name = 'product_id'
  AND fk_cols.ref_col_name = 'id'
SQL;

        $sql = strtr($sql, ['{db_name}' => $this->connection->getDatabase()]);
        return $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function up(Schema $schema) : void
    {
        $foreign_key_ids = $this->getForeignKeyIds();
        foreach ($foreign_key_ids as $id) {
            $this->addSql(sprintf("ALTER TABLE pim_catalog_completeness DROP FOREIGN KEY %s", substr($id, strpos($id, '/') + 1)));
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
