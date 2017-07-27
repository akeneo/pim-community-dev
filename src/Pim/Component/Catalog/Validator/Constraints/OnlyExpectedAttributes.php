<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OnlyExpectedAttributes extends Constraint
{
    public const ATTRIBUTE_UNEXPECTED = 'pim_catalog.constraint.can_have_family_variant_unexpected_attribute';
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_only_expected_attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
