<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Range as BaseRange;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Range extends BaseRange
{
    public $minDateMessage     = 'This date should be {{ limit }} or after.';
    public $maxDateMessage     = 'This date should be {{ limit }} or before.';
    public $invalidDateMessage = 'This value is not a valid date.';
}
