<?php

declare (strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Query\SelectAttributeOptionCodesByIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class SelectAttributeOptionCodesByIdentifiersQuery implements SelectAttributeOptionCodesByIdentifiersQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return an array of attribute option codes.
     *
     * @param string[] $attributeOptionCodes
     *
     * @return string[]
     */
    public function execute(string $attributeCode, array $attributeOptionCodes): array
    {
        $sql = <<<'SQL'
            SELECT ao.code as attribute_option_code
            FROM pim_catalog_attribute_option ao
            INNER JOIN pim_catalog_attribute a
                ON a.id = ao.attribute_id
            WHERE a.code = :attribute_code
            AND ao.code IN (:attribute_option_codes);
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'attribute_code' => $attributeCode,
                'attribute_option_codes' => $attributeOptionCodes,
            ],
            [
                'attribute_code' => \PDO::PARAM_STR,
                'attribute_option_codes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        return array_map(
            function ($row) {
                return $row['attribute_option_code'];
            },
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }
}
