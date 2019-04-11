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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Doctrine\DBAL\Connection;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class AttributeOptionRepository implements AttributeOptionRepositoryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findOneByIdentifier(AttributeCode $attributeCode, string $attributeOptionCode): ?AttributeOption
    {
        $sql = <<<'SQL'
            SELECT
                a.code as attribute_code,
                ao.code as attribute_option_code,
                (
                    SELECT JSON_OBJECTAGG(aov.locale_code, aov.value)
                    FROM pim_catalog_attribute_option_value aov
                    WHERE aov.option_id = ao.id
                    AND aov.value IS NOT NULL
                ) as translations
            FROM pim_catalog_attribute_option ao
            INNER JOIN pim_catalog_attribute a
                ON a.id = ao.attribute_id
            WHERE a.code = :attribute_code
            AND ao.code = :attribute_option_code
            LIMIT 1;
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'attribute_code' => (string)$attributeCode,
                'attribute_option_code' => $attributeOptionCode,
            ],
            [
                'attribute_code' => \PDO::PARAM_STR,
                'attribute_option_code' => \PDO::PARAM_STR,
            ]
        );

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (false === $row) {
            return null;
        }

        return $this->buildAttributeOption($row);
    }

    public function findByCodes(array $attributeOptionCodes): array
    {
        $sql = <<<'SQL'
            SELECT
                a.code as attribute_code,
                ao.code as attribute_option_code,
                (
                    SELECT JSON_OBJECTAGG(aov.locale_code, aov.value)
                    FROM pim_catalog_attribute_option_value aov
                    WHERE aov.option_id = ao.id
                    AND aov.value IS NOT NULL
                ) as translations
            FROM pim_catalog_attribute_option ao
            INNER JOIN pim_catalog_attribute a
                ON a.id = ao.attribute_id
            WHERE ao.code IN (:attribute_option_codes);
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'attribute_option_codes' => $attributeOptionCodes,
            ],
            [
                'attribute_option_codes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        return $this->buildAttributeOptionCollection(
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    private function buildAttributeOption(array $row): AttributeOption
    {
        $translations = isset($row['translations']) ? json_decode($row['translations'], true) : [];

        return new AttributeOption(
            $row['attribute_option_code'],
            new AttributeCode($row['attribute_code']),
            $translations
        );
    }

    /**
     * @return AttributeOption[]
     */
    private function buildAttributeOptionCollection(array $rows): array
    {
        return array_map(
            function ($row) {
                return $this->buildAttributeOption($row);
            },
            $rows
        );
    }
}
