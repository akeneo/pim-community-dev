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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface RecordExistsInterface
{
    public function withIdentifier(RecordIdentifier $recordIdentifier): bool;

    public function withReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $code): bool;

    /**
     * TODO: PIM-8405 This method should be removed from the Reference Entity bounded context after split
     */
    public function withCode(RecordCode $code): bool;
}
