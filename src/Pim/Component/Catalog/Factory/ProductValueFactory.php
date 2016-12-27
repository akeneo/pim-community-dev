<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class ProductValueFactory
{
    /** @var string */
    private $productValueClass;

    /**
     * @param string $productValueClass
     */
    public function __construct($productValueClass)
    {
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->productValueClass = $productValueClass;
    }

    public function createEmpty(AttributeInterface $attribute, $channelCode, $localeCode)
    {
        /** @var ProductValueInterface $value */
        $value = new $this->productValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        return $value;
    }
}
