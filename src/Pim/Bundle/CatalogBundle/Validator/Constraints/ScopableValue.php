<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if scopable value has existing scope, and if not scopable value has no scope
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableValue extends Constraint
{
    /** @var string */
    public $expectedScopeMessage = 'Product value for attribute "%attribute%" must be defined with a scope';

    /** @var string */
    public $unexpectedScopeMessage = 'Product value for attribute "%attribute%" must be defined without a scope';

    /** @var string */
    public $inexistingScopeMessage = 'Inexisting channel "%channel%" is used to scope the product value "%attribute%"';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_scopable_value_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
