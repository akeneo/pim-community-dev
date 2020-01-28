<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributeAsMainTitleValueFromProductIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class GetAttributeAsMainTitleValueFromProductId implements GetAttributeAsMainTitleValueFromProductIdInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductId $productId): array
    {
        $query = <<<SQL
SELECT JSON_OBJECT(pca.code, JSON_EXTRACT(pcp.raw_values, CONCAT('$.', pca.code))) AS value
FROM pim_catalog_product AS pcp
    INNER JOIN pim_catalog_family AS pcf ON (pcp.family_id = pcf.id)
    INNER JOIN pim_catalog_attribute AS pca
        ON (pcf.label_attribute_id = pca.id)
        AND pca.is_localizable = 1
        AND pca.attribute_type = 'pim_catalog_text'
WHERE pcp.id = :product_id
   AND JSON_CONTAINS_PATH(pcp.raw_values, 'one', CONCAT('$.', pca.code))
SQL;

        $statement = $this->db->executeQuery(
            $query,
            [
                'product_id' => $productId->toInt(),
            ],
            [
                'product_id' => \PDO::PARAM_INT,
            ]
        );

        $value = $statement->fetch(FetchMode::COLUMN, 0);

        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }
}
