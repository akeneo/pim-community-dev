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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * Bulk hydrator of RecordItems.
 * We take the advantage of bulk to unify heavy operations such as retrieving linked record labels.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class BulkRecordItemHydrator
{
    /** @var RecordItemHydratorInterface */
    private $recordItemHydrator;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    /** @var FindRecordLabelsByIdentifiersInterface */
    private $findRecordLabelsByIdentifiers;

    public function __construct(
        RecordItemHydratorInterface $recordItemHydrator,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        FindRecordLabelsByIdentifiersInterface $findRecordLabelsByIdentifiers
    ) {
        $this->recordItemHydrator = $recordItemHydrator;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
        $this->findRecordLabelsByIdentifiers = $findRecordLabelsByIdentifiers;
    }

    public function hydrateAll(array $rows, RecordQuery $query): array
    {
        $recordItems = [];

        $referenceEntityFilter = $query->getFilter('reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityFilter['value']);

        $labelsIndexedByRecordIdentifier = $this->getLabelsForIdentifier($rows, $referenceEntityIdentifier);

        foreach ($rows as $row) {
            $recordItems[] = $this->recordItemHydrator->hydrate($row, $query, ['labels' => $labelsIndexedByRecordIdentifier]);
        }

        return $recordItems;
    }

    private function getLabelsForIdentifier(array $rows, ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $recordIdentifiers = [];
        $recordLinkValueKeys = $this->findValueKeysByAttributeType->find(
            $referenceEntityIdentifier,
            ['record', 'record_collection']
        );
        $mask = array_flip($recordLinkValueKeys);

        foreach ($rows as $row) {
            $valueCollection = json_decode($row['value_collection'], true);
            $rawRecordValues = array_intersect_key($valueCollection, $mask);

            foreach ($rawRecordValues as $rawValue) {
                $data = is_array($rawValue['data']) ? $rawValue['data'] : [$rawValue['data']];
                $recordIdentifiers = array_merge($recordIdentifiers, $data);
            }
        }

        $labelsIndexedByRecordIdentifier = $this->findRecordLabelsByIdentifiers->find($recordIdentifiers);

        return $labelsIndexedByRecordIdentifier;
    }
}
