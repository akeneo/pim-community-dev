<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * Create a variant product from a product.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateVariantProduct
{
    /** @var string */
    private $variantProductClassName;

    /**
     * @param string $variantProductClassName
     */
    public function __construct(string $variantProductClassName)
    {
        $this->variantProductClassName = $variantProductClassName;
    }

    /**
     * All product data are copied from the product to the product variant product. Its values are filtered too because
     * we need to remove values from its parent.
     *
     * @param ProductInterface      $product
     * @param ProductModelInterface $parent
     *
     * @return VariantProductInterface
     *
     * @throws \InvalidArgumentException
     */
    public function from(ProductInterface $product, ProductModelInterface $parent)
    {
        if ($product->getFamily() !== $parent->getFamily()) {
            throw new \InvalidArgumentException('Product and product model families should be the same.');
        }

        $variantProduct = $this->createVariantProduct($product);
        $parentAttributes = $parent->getFamilyVariant()->getAttributes();
        $filteredValues = $product->getValues()->filter(
            function (ValueInterface $value) use ($parentAttributes) {
                return $parentAttributes->contains($value->getAttribute());
            }
        );

        $variantProduct->setParent($parent);
        $variantProduct->setValues($filteredValues);
        $variantProduct->setFamilyVariant($parent->getFamilyVariant());

        return $variantProduct;
    }

    /**
     * Copy product data to variant product.
     *
     * @param ProductInterface $product
     *
     * @return VariantProductInterface
     */
    private function createVariantProduct(ProductInterface $product): VariantProductInterface
    {
        /** @var VariantProductInterface $variantProduct */
        $variantProduct = new $this->variantProductClassName();

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
        $variantProduct->setUniqueData($product->getUniqueData());

        return $variantProduct;
    }
}
