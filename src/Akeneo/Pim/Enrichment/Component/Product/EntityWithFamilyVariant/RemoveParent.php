<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

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
        Assert::true($product->isVariant(), 'Cannot remove parent from a non variant product');

        if (null === $product->getId()) {
            // irrelevant at product creation
            return;
        }

        $allValues = $product->getValues();
        $allCategories = $product->getCategories();
        $allAssociations = $product->getAllAssociations();
        $this->mergeQuantifiedAssociations($product);

        $parent = $product->getParent();
        $parent->removeProduct($product);
        $product->setParent(null);

        $product->setValues($allValues);

        foreach ($allCategories as $category) {
            $product->addCategory($category);
        }

        foreach ($allAssociations as $association) {
            $associationTypeCode = $association->getAssociationType()->getCode();
            foreach ($association->getProducts() as $associatedProduct) {
                $product->addAssociatedProduct($associatedProduct, $associationTypeCode);
            }
            foreach ($association->getProductModels() as $associatedProductModel) {
                $product->addAssociatedProductModel($associatedProductModel, $associationTypeCode);
            }
            foreach ($association->getGroups() as $associatedGroup) {
                $product->addAssociatedGroup($associatedGroup, $associationTypeCode);
            }
        }

        $this->eventDispatcher->dispatch(new ParentHasBeenRemovedFromVariantProduct($product, $parent->getCode()));
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
