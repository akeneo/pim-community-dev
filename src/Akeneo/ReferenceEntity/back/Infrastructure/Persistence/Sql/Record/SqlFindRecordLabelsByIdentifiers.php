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

use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRecordLabelsByIdentifiers implements FindRecordLabelsByIdentifiersInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var string[] */
    private $attributesAsLabel;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $recordIdentifiers): array
    {
        $fetch = <<<SQL
SELECT 
    result.record_identifier as identifier,
    result.record_code as code,
    JSON_OBJECTAGG(result.locale_code, result.label) as labels
FROM (
    SELECT 
        labels_result.record_identifier,
        labels_result.record_code,
        labels_result.locale_code,
        labels_result.label
    FROM (
        SELECT 
            r.identifier as record_identifier,
            r.code as record_code,
            locales.code as locale_code,
            JSON_EXTRACT(
                value_collection,
                CONCAT('$.', '"', re.attribute_as_label, '_', locales.code, '"', '.data')
            ) as label
        FROM akeneo_reference_entity_record r
        JOIN akeneo_reference_entity_reference_entity re
            ON r.reference_entity_identifier = re.identifier
        CROSS JOIN pim_catalog_locale as locales
        WHERE locales.is_activated = true
        AND r.identifier IN (:recordIdentifiers)
    ) as labels_result
) as result
GROUP BY identifier;
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

        return array_reduce($statement->fetchAll(PDO::FETCH_ASSOC), function ($labelsIndexedByRecord, $current) {
            $labelsIndexedByRecord[$current['identifier']] = [
                'labels' => json_decode($current['labels'], true),
                'code' => $current['code'],
            ];

            return $labelsIndexedByRecord;
        }, []);
    }
}
