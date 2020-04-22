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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SelectExactMatchAttributeCodeQuery implements SelectExactMatchAttributeCodeQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param FamilyCode $familyCode
     * @param string[] $franklinAttributeLabels
     *
     * @return array
     */
    public function execute(FamilyCode $familyCode, array $franklinAttributeLabels): array
    {
        $allowedAttributeTypes = array_keys(AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS);

        $query = <<<SQL
SELECT DISTINCT a.code, at.label
FROM pim_catalog_attribute a
INNER JOIN pim_catalog_family_attribute fa ON(a.id = fa.attribute_id)
INNER JOIN pim_catalog_family f ON(fa.family_id = f.id)
LEFT JOIN pim_catalog_attribute_translation at ON(at.foreign_key = a.id AND at.locale LIKE "en_%")
WHERE f.code = :family_code
AND a.is_scopable = 0
AND a.is_localizable = 0
AND a.attribute_type IN(:allowed_attribute_types)
AND NOT EXISTS (SELECT 1 from pim_catalog_attribute_locale WHERE attribute_id = a.id)
AND (a.code IN(:franklin_attribute_codes) OR at.label IN(:franklin_attribute_labels))
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'allowed_attribute_types' => $allowedAttributeTypes,
                'family_code' => (string) $familyCode,
                'franklin_attribute_codes' => array_map(function (string $attributeLabel) {
                    return strval(AttributeCode::fromLabel($attributeLabel));
                }, $franklinAttributeLabels),
                'franklin_attribute_labels' => $franklinAttributeLabels,
            ],
            [
                'allowed_attribute_types' => Connection::PARAM_STR_ARRAY,
                'family_code' => \PDO::PARAM_STR,
                'franklin_attribute_codes' => Connection::PARAM_STR_ARRAY,
                'franklin_attribute_labels' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $searchResults = $statement->fetchAll();

        $result = array_fill_keys($franklinAttributeLabels, null);

        foreach ($franklinAttributeLabels as $franklinAttributeLabel) {
            foreach ($searchResults as $searchResult) {
                if (strcasecmp($franklinAttributeLabel, $searchResult['code']) === 0 ||
                    strcasecmp(strval(AttributeCode::fromLabel($franklinAttributeLabel)), $searchResult['code']) === 0 ||
                    ($searchResult['label'] !== null && strcasecmp($franklinAttributeLabel, $searchResult['label']) === 0)
                ) {
                    $result[$franklinAttributeLabel] = $searchResult['code'];
                    break;
                }
            }
        }

        return $result;
    }
}
