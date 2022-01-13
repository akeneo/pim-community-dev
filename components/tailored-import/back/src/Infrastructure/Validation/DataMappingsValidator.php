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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class DataMappingsValidator extends ConstraintValidator
{
    private const MAX_DATA_MAPPING_COUNT = 500;

    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, [
            new Type('array'),
            new Count([
                'max' => self::MAX_DATA_MAPPING_COUNT,
                'maxMessage' => DataMappings::MAX_DATA_MAPPING_COUNT_REACHED,
            ])
        ]);
    }
}
