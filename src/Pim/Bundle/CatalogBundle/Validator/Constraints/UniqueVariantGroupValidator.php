<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Unique variant group validator
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueVariantGroupValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof ProductInterface) {
            $productVariantGroup = null;
            foreach ($value->getGroups() as $group) {
                if ($group->getType()->isVariant() && null !== $productVariantGroup) {
                    $this->context->addViolation(
                        $constraint->message,
                        array(
                            '%group_one%' => $productVariantGroup,
                            '%group_two%' => $group,
                            '%product%'   => $value->getIdentifier()
                        )
                    );
                } elseif ($group->getType()->isVariant()) {
                    $productVariantGroup = $group;
                }
            }
        }
    }
}
