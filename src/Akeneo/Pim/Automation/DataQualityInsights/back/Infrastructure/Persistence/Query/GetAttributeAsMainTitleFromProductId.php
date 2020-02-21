<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributeAsMainTitleFromProductIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

class GetAttributeAsMainTitleFromProductId implements GetAttributeAsMainTitleFromProductIdInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductId $productId): ?AttributeCode
    {
        $query = <<<SQL
SELECT pca.code
FROM pim_catalog_product AS pcp
    INNER JOIN pim_catalog_family AS pcf ON (pcp.family_id = pcf.id)
    INNER JOIN pim_catalog_attribute AS pca
        ON (pcf.label_attribute_id = pca.id)
        AND pca.is_localizable = 1
        AND pca.attribute_type = :attribute_type
WHERE pcp.id = :product_id
SQL;

        $statement = $this->db->executeQuery(
            $query,
            [
                'product_id' => $productId->toInt(),
                'attribute_type' => AttributeTypes::TEXT
            ],
            [
                'product_id' => \PDO::PARAM_INT,
                'attribute_type' => \PDO::PARAM_STR,
            ]
        );

        $titleCode = $statement->fetchColumn();

        if (false === $titleCode) {
            return null;
        }

        return new AttributeCode(strval($titleCode));
    }
}
