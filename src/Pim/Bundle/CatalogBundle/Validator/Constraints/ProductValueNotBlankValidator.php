<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\Channel;

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
            if (!$this->validatePrices($value, $channel)) {
                $this->context->addViolation($constraint->messageNotBlank);
            }
        }
    }

    /**
     * Validate that prices contains expected currencies
     *
     * @param ProductValueInterface $value
     * @param Channel               $channel
     *
     * @return boolean
     */
    protected function validatePrices(ProductValueInterface $value, Channel $channel)
    {
        $expectedCurrencies = array_map(
            function ($currency) {
                return $currency->getCode();
            },
            $channel->getCurrencies()->toArray()
        );
        foreach ($expectedCurrencies as $currency) {
            foreach ($value->getData() as $price) {
                if ($price->getCurrency() === $currency) {
                    if ($price->getData() === null) {

                        return false;
                    }
                }
            }
        }

        return true;
    }
}
