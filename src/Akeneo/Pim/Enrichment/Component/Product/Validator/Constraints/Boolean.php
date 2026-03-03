<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Boolean constraint
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Boolean extends Constraint
{
    const NOT_BOOLEAN_ERROR = '541d44c2-0cec-4b43-87f7-5df101b2a951';

    public string $message = 'pim_catalog.constraint.boolean.boolean_value_is_required';

    public ?string $attributeCode;

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
