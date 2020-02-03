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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeOptionsByAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

class SelectAttributesToApplyQuery implements SelectAttributesToApplyQueryInterface
{
    /** @var Connection */
    private $connection;

    /** @var SelectAttributeOptionsByAttributeCodeQueryInterface */
    private $selectAttributeOptions;

    public function __construct(Connection $connection, SelectAttributeOptionsByAttributeCodeQueryInterface $selectAttributeOptions)
    {
        $this->connection = $connection;
        $this->selectAttributeOptions = $selectAttributeOptions;
    }

    public function execute(array $attributeCodes): array
    {
        $searchResults = $this->executeQuery($attributeCodes);

        $attributes = [];
        foreach ($searchResults as $attribute) {
            $attributes[] = $this->buildAttribute($attribute);
        }

        return $attributes;
    }

    private function executeQuery(array $attributeCodes): array
    {
        $sql = <<<'SQL'
            SELECT 
                DISTINCT attribute.code, attribute.attribute_type as `type`, attribute.metric_family, 
                attribute.default_metric_unit AS unit,
            (SELECT JSON_OBJECTAGG(IFNULL(locale, 0), label) FROM pim_catalog_attribute_translation at WHERE foreign_key = attribute.id AND at.locale LIKE "en_%") AS labels
            FROM pim_catalog_attribute AS attribute
            WHERE attribute.code IN(:attributeCodes)
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        );

        $searchResults = $statement->fetchAll();

        return $searchResults;
    }

    private function buildAttribute(array $attribute): array
    {
        if (! empty($attribute['labels'])) {
            $translations = json_decode($attribute['labels'], true);
            $attribute['labels'] = array_map(function ($label, $locale) {
                return [
                    'locale' => $locale,
                    'label' => $label,
                ];
            }, $translations, array_keys($translations));
        } else {
            $attribute['labels'] = [];
        }

        if ($attribute['type'] !== AttributeTypes::METRIC) {
            unset($attribute['metric_family']);
            unset($attribute['unit']);
        }

        if ($attribute['type'] === AttributeTypes::OPTION_SIMPLE_SELECT || $attribute['type'] === AttributeTypes::OPTION_MULTI_SELECT) {
            $attributeOptions = $this->selectAttributeOptions->execute($attribute['code']);
            if (! empty($attributeOptions)) {
                $attribute['options'] = $attributeOptions;
            }
        }

        $attribute['type'] = AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS[$attribute['type']] ?? $attribute['type'];

        return $attribute;
    }
}
