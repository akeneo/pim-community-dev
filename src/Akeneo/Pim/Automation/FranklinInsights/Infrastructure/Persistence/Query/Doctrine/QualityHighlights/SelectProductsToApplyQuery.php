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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Product;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectProductsToApplyQueryInterface;
use Doctrine\DBAL\Connection;

class SelectProductsToApplyQuery implements SelectProductsToApplyQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productsIds): array
    {
        $sql = <<<SQL
SELECT p.id, f.code as family_code, p.raw_values
FROM pim_catalog_product as p
INNER JOIN pim_catalog_family f on p.family_id = f.id
WHERE p.id IN (:productsIds)
ORDER BY family_code
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            ['productsIds' => $productsIds],
            ['productsIds' => Connection::PARAM_INT_ARRAY]
        );

        $products = $statement->fetchAll();

        return array_values(array_map(function ($product) {
            return new Product(
                new ProductId((int) $product['id']),
                new FamilyCode($product['family_code']),
                json_decode($product['raw_values'], true)
            );
        }, $products));
    }
}
