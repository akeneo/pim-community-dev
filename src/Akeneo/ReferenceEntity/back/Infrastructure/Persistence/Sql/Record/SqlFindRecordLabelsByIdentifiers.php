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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRecordLabelsByIdentifiers implements FindRecordLabelsByIdentifiersInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private FindLocales $findLocales
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $recordIdentifiers): array
    {
        $activatedLocaleCodes = array_map(static fn (Locale $locale) => $locale->getCode(), $this->findLocales->findAllActivated());

        $fetch = <<<SQL
SELECT
    r.identifier,
    r.code,
    r.value_collection,
    re.attribute_as_label
FROM 
    akeneo_reference_entity_record r
    JOIN akeneo_reference_entity_reference_entity re ON r.reference_entity_identifier = re.identifier
WHERE 
    r.identifier IN (:recordIdentifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'recordIdentifiers' => $recordIdentifiers,
            ],
            [
                'recordIdentifiers' => Connection::PARAM_STR_ARRAY
            ]
        );

        $records = $statement->fetchAllAssociative();

        $labelsIndexedByRecord = [];
        foreach ($records as $record) {
            $labelsIndexedByRecord[$record['identifier']] = [
                'code' => $record['code'],
                'labels' => $this->getLabelsIndexedByLocale($record, $activatedLocaleCodes),
            ];
        }

        return $labelsIndexedByRecord;
    }

    private function getLabelsIndexedByLocale(array $record, array $activatedLocaleCodes): array
    {
        $values = json_decode($record['value_collection'], true);
        $labels = [];

        foreach ($activatedLocaleCodes as $activatedLocaleCode) {
            $key = sprintf('%s_%s', $record['attribute_as_label'], $activatedLocaleCode);
            $labels[$activatedLocaleCode] = key_exists($key, $values)
                ? $values[$key]['data']
                : null;
        }

        return $labels;
    }
}
