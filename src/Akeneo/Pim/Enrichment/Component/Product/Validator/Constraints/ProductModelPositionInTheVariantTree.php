<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that, if the family variant has 2 levels (meaning 2 attribute sets),
 * a sub product model can only have a root product model as parent, and if the
 * family variant has 1 level (meaning 1 attribute set), the product model has no
 * parent.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelPositionInTheVariantTree extends Constraint
{
    public const INVALID_PARENT = 'pim_catalog.constraint.cannot_have_product_model_as_parent';
    public const CANNOT_HAVE_PARENT = 'pim_catalog.constraint.cannot_have_parent';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_has_a_root_product_model_as_parent';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
