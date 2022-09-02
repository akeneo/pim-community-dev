<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductModelIdsFromProductModelCodesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelIdsFromProductModelCodesQuery implements GetProductModelIdsFromProductModelCodesQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $productModelCodes): array
    {
        $query = <<<SQL
SELECT code, id FROM pim_catalog_product_model
WHERE code IN (:productModelCodes)
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $productModelIds = [];
        while ($productModel = $stmt->fetchAssociative()) {
            $productModelIds[$productModel['code']] = $this->idFactory->create((string)$productModel['id']);
        }

        return $productModelIds;
    }
}
