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

        $variantProduct = $this->createVariantProduct($product);

        $parentValues = $parent->getValues();
        $filteredValues = $product->getValues()->filter(
            function (ValueInterface $value) use ($parentValues) {
                return !$parentValues->contains($value);
            }
        );

        $variantProduct->setValues($filteredValues);

        return $variantProduct;
    }

    /**
     * @param ProductInterface $product
     *
     * @return VariantProductInterface
     */
    private function createVariantProduct(ProductInterface $product)
    {
        //TODO: inject the class
        $variantProduct = new VariantProduct();

        $valueIdentifier = $product->getValues()->filter(
            function (ValueInterface $value) {
                return AttributeTypes::IDENTIFIER === $value->getAttribute()->getType();
            }
        )->first();

        $variantProduct->setId($product->getId());
        $variantProduct->setIdentifier($valueIdentifier);
        $variantProduct->setGroups($product->getGroups());
        $variantProduct->setAssociations($product->getAssociations());
        $variantProduct->setEnabled($product->isEnabled());
        $variantProduct->setCompletenesses($product->getCompletenesses());
        $variantProduct->setFamily($product->getFamily());
        $variantProduct->setCategories($product->getCategories());
        $variantProduct->setCreated($product->getCreated());
        $variantProduct->setUpdated($product->getUpdated());

        return $variantProduct;
    }
}
