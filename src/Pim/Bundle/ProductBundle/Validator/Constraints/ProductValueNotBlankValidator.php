<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Model\ProductValueInterface;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;

/**
 * Validate if a product value is not null and not empty
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNotBlankValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null or $value === false) {
            $this->context->addViolation($constraint->messageNotNull);
        }

        if (!$value instanceof ProductValueInterface) {
            return;
        }

        $data = $value->getData();

        if ($data === null) {
            $this->context->addViolation($constraint->messageNotNull);

            return;
        }

        if ($data === '') {
            $this->context->addViolation($constraint->messageNotBlank);

            return;
        }

        if ($data instanceof Collection and count($data) === 0) {
            $this->context->addViolation($constraint->messageNotBlank);

            return;
        }

        if ($value->getAttribute() and $value->getAttribute()->getAttributeType() === 'pim_product_price_collection') {
            $channel = $constraint->getChannel();
            $expectedCurrencies = array_map(
                function ($currency) {
                    return $currency->getCode();
                },
                $channel->getCurrencies()->toArray()
            );
            foreach ($expectedCurrencies as $currency) {
                foreach ($data as $price) {
                    if ($price->getCurrency() === $currency) {
                        if ($price->getData() === null) {
                            $this->context->addViolation($constraint->messageNotBlank);

                            return;
                        }
                    }
                }
            }
        }
    }
}
