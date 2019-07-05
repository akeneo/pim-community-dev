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


namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodesFromOtherFamiliesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SelectExactMatchAttributeCodesFromOtherFamiliesQuery implements SelectExactMatchAttributeCodesFromOtherFamiliesQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(FamilyCode $familyCode, array $franklinAttributeLabels): array
    {
        $allowedAttributeTypes = array_keys(AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS);

        $query = <<<SQL
SELECT DISTINCT a.code, at.label
FROM pim_catalog_attribute a
LEFT JOIN pim_catalog_attribute_translation at ON(at.foreign_key = a.id AND at.locale LIKE "en_%")
LEFT JOIN pim_catalog_family_attribute fa ON(a.id = fa.attribute_id)
LEFT JOIN pim_catalog_family f ON(fa.family_id = f.id AND f.code != :family_code)
WHERE a.is_scopable = 0
AND a.is_localizable = 0
AND a.attribute_type IN(:allowed_attribute_types)
AND NOT EXISTS (SELECT 1 from pim_catalog_attribute_locale WHERE attribute_id = a.id) # Checks if this attribute code is not defined as "locale specific attribute"
AND (a.code IN(:franklin_attribute_labels) OR at.label IN(:franklin_attribute_labels))
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'allowed_attribute_types' => $allowedAttributeTypes,
                'family_code' => (string) $familyCode,
                'franklin_attribute_labels' => $franklinAttributeLabels,
            ],
            [
                'allowed_attribute_types' => Connection::PARAM_STR_ARRAY,
                'family_code' => \PDO::PARAM_STR,
                'franklin_attribute_labels' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $searchResults = $statement->fetchAll();
        $matchedAttributes = array_fill_keys($franklinAttributeLabels, null);
        foreach ($matchedAttributes as $franklinAttributeLabel => $matchedAttribute) {
            $matchedAttributes[$franklinAttributeLabel] = $this->getMatchedAttributeCode($franklinAttributeLabel, $searchResults);
        }

        return $matchedAttributes;
    }

    private function getMatchedAttributeCode($franklinAttributeLabel, array $pimAttributes): ?string
    {
        $matchedAttributes = array_filter($pimAttributes, function ($attribute) use ($franklinAttributeLabel) {
            return (
                strcasecmp($franklinAttributeLabel, $attribute['code']) === 0 ||
                strcasecmp($franklinAttributeLabel, $attribute['label']) === 0
            );
        });

        $matchedAttribute = current($matchedAttributes);

        return $matchedAttribute['code'] ?? null;
    }
}
