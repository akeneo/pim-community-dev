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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;

class FilterRecordsByCompleteness extends Constraint
{
    public const CHANNEL_SHOULD_EXIST = 'Channel "channel_identifier" does not exist.';

    public const LOCALE_SHOULD_BE_ACTIVATED = 'Locale "locale_identifier" is not activated for the channel "channel_identifier".';
    public const LOCALES_SHOULD_BE_ACTIVATED = 'Locales "locale_identifier" are not activated for the channel "channel_identifier".';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_referenceentity.infrastructure.validation.record.filter_records_by_completeness';
    }
}
