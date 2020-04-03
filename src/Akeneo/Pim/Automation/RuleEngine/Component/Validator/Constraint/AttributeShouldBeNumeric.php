<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class AttributeShouldBeNumeric extends Constraint
{
    // TODO RUL-59: Update error message
    public $message = 'Invalid attribute type for "%attribute_code%", expected a number or price collection attribute';

    public function validatedBy()
    {
        return 'pimee_attribute_should_be_numeric_validator';
    }
}
