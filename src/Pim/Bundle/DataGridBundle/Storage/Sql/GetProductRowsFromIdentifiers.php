<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Storage\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductRowsFromIdentifiers implements CursorableRepositoryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $sql = <<<SQL
            SELECT 
                p.id,
                p.identifier,
                p.family_id,
                p.is_enabled,
                p.raw_values,
                p.created,
                p.updated,
                pm.code as product_model_code
            FROM
                pim_catalog_product p
                LEFT JOIN pim_catalog_product_model pm ON p.product_model_id = pm.id 
            WHERE identifier IN (:identifiers)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $platform = $this->connection->getDatabasePlatform();

        $products = [];
        foreach ($rows as $row) {
            $products[] = new ReadModel\Row(
                $row['identifier'],
                $row['family_id'],
                ['group_1', 'group_2'],
                Type::getType(Type::BOOLEAN)->convertToPHPValue($row['is_enabled'], $platform),
                Type::getType(Type::DATETIME)->convertToPhpValue($row['created'], $platform),
                Type::getType(Type::DATETIME)->convertToPhpValue($row['updated'], $platform),
                null,
                null,
                10,
                IdEncoder::PRODUCT_TYPE,
                (int) $row['id'],
                IdEncoder::encode(IdEncoder::PRODUCT_TYPE, (int) $row['id']),
                true,
                null,
                $row['product_model_code'],
                new ValueCollection([])
            );

        }

        return $products;
    }
}
