<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamilyVariant;

use Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
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

    /** @var CreateVariantProduct */
    private $createVariantProduct;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param CreateVariantProduct            $createVariantProduct
     * @param EventDispatcherInterface        $eventDispatcher
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        CreateVariantProduct $createVariantProduct,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->createVariantProduct = $createVariantProduct;
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

        $variantProduct = $this->createVariantProduct->from($product, $productModel);

        $this->eventDispatcher->dispatch(
            ParentHasBeenAddedToProduct::EVENT_NAME,
            new ParentHasBeenAddedToProduct($variantProduct, $parentProductModelCode)
        );

        return $variantProduct;
    }
}
