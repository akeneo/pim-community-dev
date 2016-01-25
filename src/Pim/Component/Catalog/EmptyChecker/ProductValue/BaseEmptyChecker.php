<?php

namespace Pim\Component\Catalog\EmptyChecker\ProductValue;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Base empty product value checker which supports all native attribute types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseEmptyChecker implements EmptyCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEmpty(ProductValueInterface $productValue)
    {
        $attributeType = $productValue->getAttribute()->getAttributeType();
        $valueData = $productValue->getData();
        $isMultiCollection = [
            AttributeTypes::OPTION_MULTI_SELECT,
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT
        ];
        if ($valueData === null || $valueData === '') {
            return true;
        } elseif ($isMultiCollection && $valueData instanceof Collection && $valueData->isEmpty()) {
            return true;
        } elseif (AttributeTypes::PRICE_COLLECTION === $attributeType) {
            $fulfilledPrice = false;
            foreach ($valueData as $price) {
                if (null !== $price->getData()) {
                    $fulfilledPrice = true;
                }
            }
            if (false === $fulfilledPrice) {
                return true;
            }
        } elseif (AttributeTypes::METRIC === $attributeType) {
            if (null === $valueData->getData()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $productValue)
    {
        return true;
    }
}
