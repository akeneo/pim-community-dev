<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230425090000_remove_duplicated_variant_product extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove duplicated variant product';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            !$this->haveDuplicatedVariantProduct(),
            'No duplicated variant product'
        );

        $this->connection->executeStatement(<<<SQL
DELETE t1
FROM pim_catalog_product AS t1, pim_catalog_product AS t2
WHERE t1.id > t2.id
  AND t1.product_model_id = t2.product_model_id
  AND t1.raw_values = t2.raw_values;
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function haveDuplicatedVariantProduct(): bool
    {
        $sql = <<<SQL
SELECT   COUNT(*) AS duplicate_nb, product_model_id, raw_values
FROM     pim_catalog_product
GROUP BY product_model_id, raw_values
HAVING   COUNT(*) > 1
SQL;

        $result = $this->connection->fetchAllAssociative($sql);

        return \count($result) > 0;
    }
}
