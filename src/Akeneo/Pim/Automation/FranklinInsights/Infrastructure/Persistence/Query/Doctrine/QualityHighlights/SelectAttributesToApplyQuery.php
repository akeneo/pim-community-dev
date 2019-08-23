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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

class SelectAttributesToApplyQuery implements SelectAttributesToApplyQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $attributeIds): array
    {
        $searchResults = $this->executeQuery($attributeIds);

        $attributes = [];
        foreach ($searchResults as $attribute) {
            $attributes[$attribute['code']] = $this->buildAttribute($attribute);
        }

        return $attributes;
    }

    private function executeQuery(array $attributeIds): array
    {
        $sql = <<<'SQL'
            SELECT 
                DISTINCT attribute.code, attribute.attribute_type, attribute.metric_family, 
                attribute.default_metric_unit AS unit,
            (SELECT JSON_OBJECTAGG(IFNULL(locale, 0), label) FROM pim_catalog_attribute_translation WHERE foreign_key = attribute.id) AS labels
            FROM pim_catalog_attribute AS attribute
            WHERE attribute.id IN(:attributeIds)
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            ['attributeIds' => $attributeIds],
            ['attributeIds' => Connection::PARAM_INT_ARRAY]
        );

        $searchResults = $statement->fetchAll();

        return $searchResults;
    }

    private function buildAttribute($attributeResult): array
    {
        $attribute = $attributeResult;

        $translations = json_decode($attributeResult['labels'], true);
        $attribute['labels'] = array_map(function ($label, $locale) {
            return [
                'locale' => $locale,
                'label' => $label,
            ];
        }, $translations, array_keys($translations));

        if ($attributeResult['attribute_type'] !== AttributeTypes::METRIC) {
            unset($attribute['metric_family']);
            unset($attribute['unit']);
        }

        return $attribute;
    }
}
