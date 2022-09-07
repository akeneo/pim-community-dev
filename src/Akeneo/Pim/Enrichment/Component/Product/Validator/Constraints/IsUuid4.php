<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsUuid4 extends Constraint
{
    public string $message = 'The version of the uuid "{{ uuid }}" should be 4, {{ version }} given.';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
