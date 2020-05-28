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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\AttributeTypesValidator;
use Symfony\Component\Validator\Constraint;

class AttributeTypes extends Constraint
{
    public $message = 'Invalid "{{ invalid_type }}" attribute type. Expected a {{ expected_types }} attribute';
    public $types = [];

    public function validatedBy()
    {
        return AttributeTypesValidator::class;
    }

    public function getRequiredOptions()
    {
        return ['types'];
    }

    public function getDefaultOption()
    {
        return 'types';
    }
}
