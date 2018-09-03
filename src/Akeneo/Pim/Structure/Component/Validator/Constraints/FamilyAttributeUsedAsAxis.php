<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Family attribute_used_as_axis constraint
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeUsedAsAxis extends Constraint
{
    /** @var string */
    public $messageAttribute = 'Attribute "%attribute%" is an axis in "%family_variant%" family variant. It must belong to the family.';

    /** @var string */
    public $propertyPath = 'attributes';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_family_attribute_used_as_axis';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
