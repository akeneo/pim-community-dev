<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * During an import, a mass action, or an update via the API you can add a parent to a product, that means that you
 * transform it into a variant product.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParent
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param EventDispatcherInterface        $eventDispatcher
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Add a parent to a product during an import, a mass action or an update from API.
     *
     * @param ProductInterface $product
     * @param string           $parentProductModelCode
     *
     * @return ProductInterface
     */
    public function to(ProductInterface $product, string $parentProductModelCode): ProductInterface
    {
        // we don't add a parent if it is a creation
        if (null === $product->getId()) {
            return $product;
        }

        if (null === $productModel = $this->productModelRepository->findOneByIdentifier($parentProductModelCode)) {
            throw new \InvalidArgumentException(
                sprintf('The given product model "%s" does not exist', $parentProductModelCode)
            );
        }

        $product->setParent($productModel);
        $product->setFamilyVariant($productModel->getFamilyVariant());
        $product->setValues($this->filterNonVariantValues($productModel, $product));

        $this->eventDispatcher->dispatch(
            ParentHasBeenAddedToProduct::EVENT_NAME,
            new ParentHasBeenAddedToProduct($product, $parentProductModelCode)
        );

        return $product;
    }

    private function filterNonVariantValues(
        ProductModelInterface $productModel,
        ProductInterface $product
    ): WriteValueCollection {
        $familyVariant = $productModel->getFamilyVariant();
        $variantAttributes = $familyVariant->getVariantAttributeSet(
            $familyVariant->getNumberOfLevel()
        )->getAttributes();

        $attributeCodes = [];

        foreach ($variantAttributes as $attribute) {
            $attributeCodes[] = $attribute->getCode();
        }

        $filteredValues = $product->getValues()->filter(
            function (ValueInterface $value) use ($attributeCodes) {
                return in_array($value->getAttributeCode(), $attributeCodes);
            }
        );

        return $filteredValues;
    }
}
