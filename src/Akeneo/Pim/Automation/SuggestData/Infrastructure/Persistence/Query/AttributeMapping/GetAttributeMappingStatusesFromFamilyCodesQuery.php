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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\AttributeMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Query\GetAttributeMappingStatusesFromFamilyCodesQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * Doctrine implementation of the GetAttributeMappingStatusesFromFamilyCodesQuery query.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetAttributeMappingStatusesFromFamilyCodesQuery implements GetAttributeMappingStatusesFromFamilyCodesQueryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $familyCodes): array
    {
        $query = <<<SQL
SELECT f.code, SUM(s.misses_mapping) as misses_mapping FROM pim_suggest_data_product_subscription s
INNER JOIN pim_catalog_product p ON s.product_id = p.id
INNER JOIN pim_catalog_family f ON p.family_id = f.id WHERE f.code IN (:familyCodes)
GROUP BY f.code;
SQL;

        $queryParameters = ['familyCodes' => $familyCodes];
        $types = ['familyCodes' => Type::SIMPLE_ARRAY];

        $statement = $this->connection->executeQuery($query, $queryParameters, $types);

        $results = $statement->fetchAll();

        $attributeStatusesByFamily = [];
        foreach ($results as $result) {
            $familyCode = $result['code'];
            $missesMapping = (bool) $result['misses_mapping'] ? Family::MAPPING_PENDING : Family::MAPPING_FULL;

            $attributeStatusesByFamily[$familyCode] = $missesMapping;
        }

        return $attributeStatusesByFamily;
    }
}
