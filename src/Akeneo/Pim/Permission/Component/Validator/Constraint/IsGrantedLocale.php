<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if a locale is granted.
 */
class IsGrantedLocale extends Constraint
{
    /** @var string */
    public $message = 'The locale "%locale%" is not granted.';
}
