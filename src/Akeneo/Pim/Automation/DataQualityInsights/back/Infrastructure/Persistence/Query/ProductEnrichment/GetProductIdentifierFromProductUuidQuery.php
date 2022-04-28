<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdentifierFromProductUuidQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdentifier;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdentifierFromProductUuidQuery implements GetProductIdentifierFromProductUuidQueryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductUuid $productUuid): ProductIdentifier
    {
        $sql = <<<SQL
SELECT identifier FROM pim_catalog_product WHERE uuid=:product_uuid;
SQL;

        $productIdentifier = $this->db->fetchOne(
            $sql,
            [
                'product_uuid' => $productUuid->toBytes(),
            ]
        );

        if (false === $productIdentifier) {
            throw new \Exception(sprintf('No identifier found for product uuid %s', $productUuid));
        }

        return new ProductIdentifier($productIdentifier);
    }
}
