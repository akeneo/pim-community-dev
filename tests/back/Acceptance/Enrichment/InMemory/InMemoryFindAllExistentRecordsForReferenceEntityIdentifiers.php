<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Test\Acceptance\Enrichment\InMemory;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

class InMemoryFindAllExistentRecordsForReferenceEntityIdentifiers implements FindAllExistentRecordsForReferenceEntityIdentifiers
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    public function __construct(InMemoryRecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function forReferenceEntityIdentifiersAndRecordCodes(array $referenceEntityIdentifiersToCodes): array
    {
        $results = [];

        /** @var Record $record */
        foreach ($this->recordRepository->all() as $record) {
            $referenceEntityIdentifier = $record->getReferenceEntityIdentifier()->normalize();
            $recordCode = $record->getCode()->normalize();
            if (isset($referenceEntityIdentifiersToCodes[$referenceEntityIdentifier])
                && in_array($recordCode, $referenceEntityIdentifiersToCodes[$referenceEntityIdentifier])
            ) {
                $results[$referenceEntityIdentifier][] = $recordCode;
            }
        }

        return $results;
    }
}
