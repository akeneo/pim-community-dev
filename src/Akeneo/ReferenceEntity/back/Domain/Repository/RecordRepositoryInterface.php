<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Repository;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface RecordRepositoryInterface
{
    public function create(Record $record): void;

    public function update(Record $record): void;

    /**
     * @throws RecordNotFoundException
     */
    public function getByIdentifier(RecordIdentifier $identifier): Record;

    /**
     * @throws RecordNotFoundException
     */
    public function getByReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $code): Record;

    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     * @param RecordCode[] $recordCodes
     */
    public function deleteByReferenceEntityAndCodes(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): void;

    /**
     * @throws RecordNotFoundException
     */
    public function deleteByReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $code): void;

    public function count(): int;

    public function countByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int;

    public function nextIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $code): RecordIdentifier;
}
