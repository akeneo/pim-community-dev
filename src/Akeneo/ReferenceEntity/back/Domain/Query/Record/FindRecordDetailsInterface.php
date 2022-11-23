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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Find a record by its composite identifier (made of its Reference Entity identifier
 * and its own Record identifier)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
interface FindRecordDetailsInterface
{
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?RecordDetails;

    /**
     * Return an array of given Record details indexed by Record code
     *
     * @param array<RecordCode> $recordCodes
     *
     * @return array<string, RecordDetails>
     */
    public function findByCodes(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array;
}
