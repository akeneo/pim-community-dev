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
 * Constraint to check if attributes are granted.
 */
class AreGrantedAttributes extends Constraint
{
    /** @var string */
    public $message = 'Attributes "%attributes%" are not granted.';
}
