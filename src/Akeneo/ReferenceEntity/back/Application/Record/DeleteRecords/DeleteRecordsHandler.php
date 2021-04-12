<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\DeleteRecords;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteRecordsHandler
{
    private RecordRepositoryInterface $recordRepository;

    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function __invoke(DeleteRecordsCommand $deleteRecordsCommand): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($deleteRecordsCommand->referenceEntityIdentifier);
        $recordCodes = array_map(fn ($code) => RecordCode::fromString($code), $deleteRecordsCommand->recordCodes);

        $this->recordRepository->deleteByReferenceEntityAndCodes($referenceEntityIdentifier, $recordCodes);
    }
}
