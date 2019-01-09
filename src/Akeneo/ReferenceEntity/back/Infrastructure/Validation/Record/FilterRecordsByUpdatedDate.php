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

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FilterRecordsByUpdatedDate extends Constraint
{
    public const DATE_SHOULD_BE_VALID = 'Property "updated" expects a string with the ISO 8601 format, "%s" given.';
    public const OPERATOR_SHOULD_BE_SUPPORTED = 'Filter on property \"updated\" does not support operator \"%s\".';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_referenceentity.infrastructure.validation.record.filter_records_by_updated_date';
    }
}
