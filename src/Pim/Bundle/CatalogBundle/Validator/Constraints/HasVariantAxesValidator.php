<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate that a product has all the axes of its variant group
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasVariantAxesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($product, Constraint $constraint)
    {
        if ($product instanceof ProductInterface && null !== $variant = $product->getVariantGroup()) {
            $missingAxisCodes = $this->getMissingAxisCodes($product, $variant);

            if (count($missingAxisCodes) > 0) {
                $this->context->buildViolation(
                    $constraint->message,
                    [
                        '%product%' => $product->getIdentifier(),
                        '%variant%' => $variant->getCode(),
                        '%axes%'    => implode(', ', $missingAxisCodes),
                    ]
                )->addViolation();
            }
        }
    }

    /**
     * Get missing axis codes of a product given a variant group
     *
     * @param ProductInterface $product
     * @param GroupInterface   $variantGroup
     *
     * @return array
     */
    protected function getMissingAxisCodes(ProductInterface $product, GroupInterface $variantGroup)
    {
        $missingAxisCodes = [];

        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            $value = $product->getValue($attribute->getCode());
            if (null === $value || null === $value->getData()) {
                $missingAxisCodes[] = $attribute->getCode();
            }
        }

        return $missingAxisCodes;
    }
}
