<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for product export filter attribute structure.
 * Filter structure are "filters" for exported columns: scope, locales & attributes.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterStructureAttribute extends Constraint
{
    /** @var string */
    public $message = 'The attribute %attributeCode% is not valid.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'filter_structure_attribute_validator';
    }
}
