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

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder;

use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindRecordLabelsByIdentifiers
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * Find records by their $recordIdentifiers then returns their labels by their record identifier
     */
    public function find(array $recordIdentifiers): array
    {
        $fetch = <<<SQL
            SELECT 
                result.record_identifier as identifier,
                result.record_code as code,
                JSON_OBJECTAGG(result.locale_code, result.label) as labels,
                result.reference_entity_identifier
            FROM (
                SELECT
                    labels_result.record_identifier,
                    labels_result.record_code,
                    labels_result.locale_code,
                    labels_result.label,
                    labels_result.reference_entity_identifier
                FROM (
                    SELECT 
                        r.identifier as record_identifier,
                        r.code as record_code,
                        locales.code as locale_code,
                        r.reference_entity_identifier as reference_entity_identifier,
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

        $recordsLabels = array_map(function ($record) {
            return new RecordLabels(
                $record['identifier'],
                json_decode($record['labels'], true),
                $record['code'],
                $record['reference_entity_identifier']
            );
        }, $statement->fetchAll());

        return $recordsLabels;
    }
}
