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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectFamiliesToApplyQueryInterface;
use Doctrine\DBAL\Connection;

class SelectFamiliesToApplyQuery implements SelectFamiliesToApplyQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $familyCodes): array
    {
        $searchResults = $this->executeQuery($familyCodes);

        $families = [];
        foreach ($searchResults as $family) {
            $family['attributes'] = json_decode($family['attributes'], true);
            $families[] = $this->buildFamilyLabels($family);
        }

        return $families;
    }

    private function executeQuery(array $familyCodes): array
    {
        $sql = <<<'SQL'
            SELECT 
              family.code, JSON_ARRAYAGG(attribute.code) AS attributes,
              (SELECT JSON_OBJECTAGG(IFNULL(locale, 0), label) FROM pim_catalog_family_translation ft WHERE foreign_key = family.id AND ft.locale LIKE "en_%") AS labels
            FROM pim_catalog_family AS family
            INNER JOIN pim_catalog_family_attribute as family_attribute ON family.id = family_attribute.family_id
            INNER JOIN pim_catalog_attribute attribute on family_attribute.attribute_id = attribute.id
            WHERE family.code IN(:familyCodes)
            GROUP BY family.code
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            ['familyCodes' => $familyCodes],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        );

        $searchResults = $statement->fetchAll();

        return $searchResults;
    }

    private function buildFamilyLabels(array $family): array
    {
        if (! empty($family['labels'])) {
            $translations = json_decode($family['labels'], true);
            $family['labels'] = array_map(function ($label, $locale) {
                return [
                    'locale' => $locale,
                    'label' => $label,
                ];
            }, $translations, array_keys($translations));
        } else {
            $family['labels'] = [];
        }

        return $family;
    }
}
