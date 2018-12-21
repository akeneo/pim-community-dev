<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SelectProductIdentifierValuesQuery implements SelectProductIdentifierValuesQueryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connexion
     */
    public function __construct(Connection $connexion)
    {
        $this->connection = $connexion;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(int $productId): ?ProductIdentifierValues
    {
        $sql = <<<SQL
SELECT p.id, JSON_OBJECTAGG(
  m.franklin_code,
  JSON_EXTRACT(p.raw_values, REPLACE('$.%identifier%."<all_channels>"."<all_locales>"', '%identifier%', a.code))
) AS mapped_identifier_values
FROM pim_catalog_product p, pim_franklin_insights_franklin_identifier_mapping m
LEFT JOIN pim_catalog_attribute a ON m.attribute_id = a.id
WHERE p.id = :product_id
GROUP BY p.id;
SQL;
        $bindParams = [
            'product_id' => $productId,
        ];
        $statement = $this->connection->executeQuery($sql, $bindParams);
        $result = $statement->fetch();

        if (!$result) {
            return null;
        }

        return new ProductIdentifierValues(json_decode($result['mapped_identifier_values'], true));
    }
}
