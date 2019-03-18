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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity\SqlFindReferenceEntityAttributeAsLabel;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRecordLabelsByCodes implements FindRecordLabelsByCodesInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var SqlFindReferenceEntityAttributeAsLabel */
    private $findReferenceEntityAttributeAsLabel;

    public function __construct(
        Connection $sqlConnection,
        SqlFindReferenceEntityAttributeAsLabel $findReferenceEntityAttributeAsLabel
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findReferenceEntityAttributeAsLabel = $findReferenceEntityAttributeAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        $fetch = <<<SQL
        SELECT code, value_collection
        FROM akeneo_reference_entity_record
        WHERE code IN (:recordCodes) AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'recordCodes' => $recordCodes,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ],
            [
                'recordCodes' => Connection::PARAM_STR_ARRAY
            ]
        );

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->extractLabelsFromResults($results, $referenceEntityIdentifier);
    }

    private function extractLabelsFromResults(
        array $results,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): array {
        if (empty($results)) {
            return [];
        }

        $attributeAsLabelReference = ($this->findReferenceEntityAttributeAsLabel)($referenceEntityIdentifier);
        if ($attributeAsLabelReference->isEmpty()) {
            throw new \Exception(
                sprintf('No attribute as label has been defined for reference entity "%s"', $referenceEntityIdentifier)
            );
        }

        $attributeAsLabel = $attributeAsLabelReference->normalize();

        $labelCollectionPerRecord = [];
        foreach ($results as $result) {
            $values = json_decode($result['value_collection'], true);
            $recordCode = $result['code'];

            $labelsIndexedPerLocale = [];
            foreach ($values as $value) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $labelsIndexedPerLocale[$value['locale']] = $value['data'];
                }
            }

            $labelCollectionPerRecord[$recordCode] = LabelCollection::fromArray($labelsIndexedPerLocale);
        }

        return $labelCollectionPerRecord;
    }
}
