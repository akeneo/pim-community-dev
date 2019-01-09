<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Counting the number of records.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface CountRecordsInterface
{
    public function forReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int;
}
