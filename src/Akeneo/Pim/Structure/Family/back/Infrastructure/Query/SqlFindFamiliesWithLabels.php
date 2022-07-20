<?php

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query;

use Akeneo\Pim\Structure\Family\ServiceAPI\Model\FamilyWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Model\FamilyWithLabelsCollection;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyQuery;
use Doctrine\DBAL\Connection;

class SqlFindFamiliesWithLabels implements FindFamiliesWithLabels
{
    public function __construct(
        private Connection      $connection,
        private FindFamilyCodes $findFamilyCodes,
    ) {
    }

    public function fromQuery(FindFamilyQuery $query): FamilyWithLabelsCollection
    {
        $familyCodes = $this->findFamilyCodes->fromQuery($query);
        $rawFamiliesWithLabels = $this->fetchFamiliesWithLabels($familyCodes);

        return $this->hydrateFamilyWithLabelsCollection($rawFamiliesWithLabels);
    }

    private function fetchFamiliesWithLabels(array $familyCodes): array
    {
        $sql = <<<SQL
            WITH translations_grouped_by_family AS (
                SELECT foreign_key, JSON_OBJECTAGG(locale, label) AS labels
                FROM pim_catalog_family_translation
                GROUP BY foreign_key
            )
            
            SELECT family.code, translation.labels
            FROM pim_catalog_family family
            LEFT JOIN translations_grouped_by_family translation ON family.id = translation.foreign_key
            WHERE family.code IN (:family_codes)
            ORDER BY family.code
        SQL;

        return $this->connection->executeQuery(
            $sql,
            ['family_codes' => $familyCodes],
            ['family_codes' => Connection::PARAM_STR_ARRAY],
        )->fetchAllAssociative();
    }

    private function hydrateFamilyWithLabelsCollection(array $rawFamiliesWithLabels): FamilyWithLabelsCollection
    {
        $familiesWithLabels = array_map(
            static fn (array $rawFamilyWithLabels) => new FamilyWithLabels(
                $rawFamilyWithLabels['code'],
                null !== $rawFamilyWithLabels['labels'] ? json_decode($rawFamilyWithLabels['labels'], true) : []
            ),
            $rawFamiliesWithLabels,
        );

        return new FamilyWithLabelsCollection($familiesWithLabels);
    }
}
