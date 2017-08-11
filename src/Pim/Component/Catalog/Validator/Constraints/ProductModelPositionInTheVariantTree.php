<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validate that a sub product model can only have a root product model as parent
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelPositionInTheVariantTree extends Constraint
{
    public const INVALID_PARENT = 'pim_catalog.constraint.has_a_root_product_model_as_parent';

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
