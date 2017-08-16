<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if a value is not longer than its database field
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetadataLength extends Constraint
{
    /** @var string */
    public $message = 'This value is too long. It should have {{ limit }} characters or less.';
}
