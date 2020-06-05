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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ProductSourceOptionsValidator;
use Symfony\Component\Validator\Constraint;

class ProductSourceOptions extends Constraint
{
    public $message = 'The {{ key }} key is irrelevant for the {{ attribute }} attribute';

    public function validatedBy()
    {
        return ProductSourceOptionsValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
