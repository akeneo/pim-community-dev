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
    public function validate($product, Constraint $constraint)
    {
        if ($product instanceof ProductInterface) {
            $variantGroups = [];
            // TODO (JJ) should be extracted in a sub method hasSeveralVG that loops over the groups and returns
            // true when 2 VG are found. This will avoid to loop over ALL the groups for nothing like here
            foreach ($product->getGroups() as $group) {
                if ($group->getType()->isVariant()) {
                    $variantGroups[] = $group;
                }
            }
            if (count($variantGroups) > 1) {
                $this->context->addViolation(
                    $constraint->message,
                    array(
                        '%groups%'  => $this->formatValues($variantGroups, ConstraintValidator::OBJECT_TO_STRING),
                        '%product%' => $product->getIdentifier()
                    )
                );
            }
        }
    }
}
