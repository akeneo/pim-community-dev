<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsNumeric extends Constraint
{
    public const IS_NUMERIC = "3ee14592-14a8-4314-836f-b6177aaf7c05";

    /** @var string */
    public $message = 'The {{ attribute }} attribute requires a number, and the submitted {{ value }} value is not.';

    /** @var string */
    public $attributeCode = '';
}
