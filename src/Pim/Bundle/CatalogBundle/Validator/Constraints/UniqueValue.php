<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for unique attribute value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValue extends Constraint
{
    /** @var string */
    public $message = 'This value is already set on another product.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_unique_value_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
