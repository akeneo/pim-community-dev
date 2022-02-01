<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

class InMemoryGetExistingRecordCodes implements GetExistingRecordCodes
{
    public function __construct(private RecordRepositoryInterface $recordRepository)
    {
    }

    public function fromReferenceEntityIdentifierAndRecordCodes(array $indexedRecordCodes): array
    {
        foreach ($indexedRecordCodes as $referenceEntityIdentifier => $recordCodes) {
            foreach ($recordCodes as $recordIndex => $recordCode) {
                try {
                    $this->recordRepository->getByReferenceEntityAndCode(
                        ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                        RecordCode::fromString($recordCode)
                    );
                } catch (RecordNotFoundException) {
                    unset($indexedRecordCodes[$referenceEntityIdentifier][$recordIndex]);
                }
            }

            if ($indexedRecordCodes[$referenceEntityIdentifier] === []) {
                unset($indexedRecordCodes[$referenceEntityIdentifier]);
            }
        }

        return $indexedRecordCodes;
    }
}
