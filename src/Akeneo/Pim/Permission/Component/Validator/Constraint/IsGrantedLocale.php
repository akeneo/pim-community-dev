<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if a locale is granted when requesting the data in the product grid.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsGrantedLocale extends Constraint
{
    /** @var string */
    public $message = 'The locale "%locale%" is not granted.';
}
