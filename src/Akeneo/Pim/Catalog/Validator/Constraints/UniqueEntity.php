<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueEntity extends Constraint
{
    public $message = 'The same identifier is already set on another product';
    public $entityClass = null;
    public $identifier = 'identifier';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_unique_product_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
