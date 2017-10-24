<?php

namespace Pim\Component\Catalog;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProduct;
use Pim\Component\Catalog\Model\VariantProductInterface;

class TurnProductIntoVariantProduct
{
    /**
     * @param ProductInterface      $product
     * @param ProductModelInterface $parent
     *
     * @return VariantProductInterface
     * @throws \Exception
     */
    public function turnInto(ProductInterface $product, ProductModelInterface $parent)
    {
        if ($product->getFamily() !== $parent->getFamily()) {
            throw new \Exception('Product and product model families should be the same.');
        }

        $variantProduct = VariantProduct::fromProduct($product);

        return $variantProduct;
    }

    public function filter(ProductInterface $product, ProductModelInterface $parent)
    {
        $parentValues = $parent->getValues();
        $filteredValues = $product->getValues()->filter(
            function (ValueInterface $value) use ($parentValues) {
                return !$parentValues->contains($value);
            }
        );

        $product->setValues($filteredValues);

        return $product;
    }
}
