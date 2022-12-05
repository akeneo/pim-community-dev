<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetExistingProductModelCodes
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @param string[] $productModelCodes
     */
    public function among(array $productModelCodes): array
    {
        Assert::allString($productModelCodes);
        $sql = <<<SQL
SELECT code FROM pim_catalog_product_model
WHERE code IN (:codes);
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'codes' => $productModelCodes,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchFirstColumn();
    }
}
