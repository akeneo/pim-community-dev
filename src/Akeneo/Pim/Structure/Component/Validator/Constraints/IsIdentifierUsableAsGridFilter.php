<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsIdentifierUsableAsGridFilter extends Constraint
{
    /** @var string */
    public $message = '"%code%" is an identifier attribute, it must be usable as grid filter';

    /** @var string */
    public $propertyPath = 'useableAsGridFilter';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
