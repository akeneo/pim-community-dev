<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class EditNumberValueCommand extends Constraint
{
    public const NUMBER_SHOULD_BE_STRING = 'pim_reference_entity.record.validation.number.should_be_string';
    public const NUMBER_SHOULD_NOT_BE_DECIMAL = 'pim_reference_entity.record.validation.number.should_not_be_decimal';
    public const NUMBER_SHOULD_BE_NUMERIC = 'pim_reference_entity.record.validation.number.should_be_numeric';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
