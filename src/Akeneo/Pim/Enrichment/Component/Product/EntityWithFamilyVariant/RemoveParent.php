<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Transforms a variant product into a non variant product. This is done by adding all the values, categories and
 * associations inherited from its ancestors
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveParent implements RemoveParentInterface
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function from(ProductInterface $product): void
    {
        if (!$product->isVariant()) {
            throw new \InvalidArgumentException('Cannot remove parent from a non variant product');
        }

        if (null === $product->getId()) {
            // irrelevant at product creation
            return;
        }

        $this->mergeValues($product);
        $this->mergeCategories($product);
        $this->mergeAssociations($product);
        $this->mergeQuantifiedAssociations($product);

        $parent = $product->getParent();
        $parent->removeProduct($product);
        $product->setParent(null);

        $this->eventDispatcher->dispatch(new ParentHasBeenRemovedFromVariantProduct($product, $parent->getCode()));
    }

    private function mergeValues(ProductInterface $product): void
    {
        // getValues() returns all product values (including values inherited from ancestors),
        // whereas setValues only sets values at the product level
        $product->setValues($product->getValues());
    }

    private function mergeCategories(ProductInterface $product): void
    {
        $productCategories = $product->getCategoriesForVariation();
        foreach ($product->getCategories() as $category) {
            if (!$productCategories->contains($category)) {
                $productCategories->add($category);
            }
        }
    }

    private function mergeAssociations(ProductInterface $product): void
    {
        foreach ($product->getAllAssociations() as $association) {
            $productAssociation = $product->getAssociationForTypeCode($association->getAssociationType()->getCode());
            if (null === $productAssociation) {
                $productAssociation = new ProductAssociation();
                $productAssociation->setAssociationType($association->getAssociationType());
                $product->addAssociation($productAssociation);
            }
            foreach ($association->getProducts() as $associatedProduct) {
                if (!$productAssociation->getProducts()->contains($associatedProduct)) {
                    $productAssociation->addProduct($associatedProduct);
                }
            }
            foreach ($association->getProductModels() as $associatedProductModel) {
                if (!$productAssociation->getProductModels()->contains($associatedProductModel)) {
                    $productAssociation->addProductModel($associatedProductModel);
                }
            }
            foreach ($association->getGroups() as $associatedGroup) {
                if (!$productAssociation->getGroups()->contains($associatedGroup)) {
                    $productAssociation->addGroup($associatedGroup);
                }
            }
        }
    }

    private function mergeQuantifiedAssociations(ProductInterface $product): void
    {
        $parent = $product->getParent();
        while (null !== $parent) {
            $product->mergeQuantifiedAssociations($parent->getQuantifiedAssociations());
            $parent = $parent->getParent();
        }
    }
}
