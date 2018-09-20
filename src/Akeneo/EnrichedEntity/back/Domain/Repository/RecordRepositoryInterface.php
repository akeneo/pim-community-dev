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

namespace Akeneo\EnrichedEntity\Domain\Repository;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;

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
    public function getByEnrichedEntityAndCode(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): Record;

    /**
     * @throws RecordNotFoundException
     */
    public function deleteByEnrichedEntityAndCode(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): void;

    public function count(): int;

    public function nextIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code):  RecordIdentifier;
}
