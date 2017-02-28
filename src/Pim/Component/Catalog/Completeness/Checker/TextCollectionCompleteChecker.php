<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class TextCollectionCompleteChecker implements ProductValueCompleteCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $collection = $productValue->getData();

        return null !== $collection && count($collection) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(ProductValueInterface $productValue)
    {
        return AttributeTypes::TEXT_COLLECTION === $productValue->getAttribute()->getAttributeType();
    }
}
