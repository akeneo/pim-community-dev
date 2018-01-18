<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;

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
     * @return ProductInterface
     *
     * @throws \InvalidArgumentException
     */
    public function from(ProductInterface $product, ProductModelInterface $parent): ProductInterface
    {
        if ($product->getFamily() !== $parent->getFamily()) {
            throw new \InvalidArgumentException('Product and product model families should be the same.');
        }

        $variantProduct = $this->createVariantProduct($product);
        $familyVariant = $parent->getFamilyVariant();
        $variantAttributes = $familyVariant->getVariantAttributeSet(
            $familyVariant->getNumberOfLevel()
        )->getAttributes();
        $filteredValues = $product->getValues()->filter(
            function (ValueInterface $value) use ($variantAttributes) {
                return $variantAttributes->contains($value->getAttribute());
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
     * @return ProductInterface
     */
    private function createVariantProduct(ProductInterface $product): ProductInterface
    {
        /** @var ProductInterface $variantProduct */
        $variantProduct = new $this->variantProductClassName();

        $identifierValue = $product->getValues()->filter(
            function (ValueInterface $value) {
                return AttributeTypes::IDENTIFIER === $value->getAttribute()->getType();
            }
        )->first();

        $variantProduct->setId($product->getId());
        $variantProduct->setIdentifier($identifierValue);
        $variantProduct->setAssociations($product->getAssociations());
        $variantProduct->setEnabled($product->isEnabled());
        $variantProduct->setCompletenesses($product->getCompletenesses());
        $variantProduct->setFamily($product->getFamily());
        $variantProduct->setCreated($product->getCreated());
        $variantProduct->setUpdated($product->getUpdated());
        $variantProduct->setUniqueData($product->getUniqueData());
        $variantProduct->setRawValues($product->getRawValues());
        // IMPORTANT: we're not assigning categories and groups, because doing so would delete the association!
        // @see AddParentAProductSubscriber and PIM-7088

        return $variantProduct;
    }
}
