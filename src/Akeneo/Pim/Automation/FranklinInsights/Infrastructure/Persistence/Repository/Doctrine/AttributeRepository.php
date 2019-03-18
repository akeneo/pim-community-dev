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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeRepository implements AttributeRepositoryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findOneByIdentifier(string $code): ?Attribute
    {
        $query = <<<SQL
        SELECT attribute.id, code, attribute_type, is_localizable, is_scopable, decimals_allowed, metric_family, default_metric_unit,
        EXISTS (SELECT id from pim_catalog_attribute_locale WHERE attribute_id = attribute.id) AS is_locale_specific,
        (SELECT JSON_OBJECTAGG(IFNULL(locale, 0), label) FROM pim_catalog_attribute_translation WHERE foreign_key = attribute.id) AS labels
        FROM pim_catalog_attribute attribute
        WHERE code = :attribute_code;
SQL;
        $statement = $this->connection->executeQuery(
            $query,
            ['attribute_code' => $code],
            ['attribute_code' => \PDO::PARAM_STR]
        );

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            return null;
        }

        return $this->buildAttribute($result);
    }

    public function findByCodes(array $codes): array
    {
        $query = <<<SQL
        SELECT attribute.id, code, attribute_type, is_localizable, is_scopable, decimals_allowed, metric_family, default_metric_unit,
        EXISTS (SELECT id from pim_catalog_attribute_locale WHERE attribute_id = attribute.id) AS is_locale_specific,
        (SELECT JSON_OBJECTAGG(IFNULL(locale, 0), label) FROM pim_catalog_attribute_translation WHERE foreign_key = attribute.id) AS labels
        FROM pim_catalog_attribute attribute
        WHERE code IN(:attribute_codes);
SQL;
        $statement = $this->connection->executeQuery(
            $query,
            ['attribute_codes' => $codes],
            ['attribute_codes' => Connection::PARAM_STR_ARRAY]
        );

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function (array $result) {
            return $this->buildAttribute($result);
        }, $results);
    }

    public function getAttributeTypeByCodes(array $codes): array
    {
        $query = <<<SQL
        SELECT code, attribute_type
        FROM pim_catalog_attribute
        WHERE code IN(:attribute_codes);
SQL;
        $statement = $this->connection->executeQuery(
            $query,
            ['attribute_codes' => $codes],
            ['attribute_codes' => Connection::PARAM_STR_ARRAY]
        );

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $attributeTypeByCodes = [];
        foreach ($results as $result) {
            $attributeTypeByCodes[$result['code']] = $result['attribute_type'];
        }

        return $attributeTypeByCodes;
    }

    /**
     * @param array $result
     *
     * @return Attribute
     */
    private function buildAttribute(array $result): Attribute
    {
        $labels = $result['labels'] ? json_decode($result['labels'], true) : [];

        return new Attribute(
            new AttributeCode($result['code']),
            (int) $result['id'],
            $result['attribute_type'],
            (bool) $result['is_scopable'],
            (bool) $result['is_localizable'],
            (bool) $result['decimals_allowed'],
            (bool) $result['is_locale_specific'],
            $labels,
            $result['metric_family'] ?? $result['metric_family'],
            $result['default_metric_unit'] ?? $result['default_metric_unit']
        );
    }
}
