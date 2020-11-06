<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdentifierFromProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdentifier;
use Doctrine\DBAL\Connection;

final class GetProductIdentifierFromProductIdQuery implements GetProductIdentifierFromProductIdQueryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(\Doctrine\DBAL\Driver\Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductId $productId): ProductIdentifier
    {
        $sql = <<<SQL
SELECT identifier FROM pim_catalog_product WHERE id=:product_id;
SQL;

        $statement = $this->db->executeQuery($sql,
            [
                'product_id' => $productId->toInt(),
            ],
            [
                'product_id' => \PDO::PARAM_INT,
            ]
        );

        $productIdentifier = $statement->fetchColumn();

        if (false === $productIdentifier) {
            throw new \Exception(sprintf('No identifier found for product id %s', $productId));
        }

        return new ProductIdentifier($productIdentifier);
    }
}
