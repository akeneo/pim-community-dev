<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Exception;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

class RecordAlreadyExistsError extends UserFacingError
{
    public static function fromRecord(Record $record): self
    {
        return new self(
            'pim_reference_entity.record.validation.code.should_be_unique',
            [
                '%reference_entity_identifier%' => $record->getReferenceEntityIdentifier()->normalize(),
                '%code%' => $record->getCode()->normalize(),
            ]
        );
    }
}
