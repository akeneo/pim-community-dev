<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Denormalization\Product;

use Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct;
use Pim\Component\Catalog\EntityWithFamily\Query;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * During an import you can add a parent to a product, that means that you transform it into a variant product.
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
    
    /** @var Query\TurnProduct */
    private $turnProduct;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param CreateVariantProduct            $createVariantProduct
     * @param Query\TurnProduct               $turnProduct
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        CreateVariantProduct $createVariantProduct,
        Query\TurnProduct $turnProduct
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->createVariantProduct = $createVariantProduct;
        $this->turnProduct = $turnProduct;
    }

    /**
     * Add a parent to a product during a import.
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

        // we do nothing if it is a product
        if ('' === $parentProductModelCode) {
            return $product;
        }
        
        if (null === $productModel = $this->productModelRepository->findOneByIdentifier($parentProductModelCode)) {
            throw new \InvalidArgumentException(
                sprintf('The given product model "%s" does not exist', $parentProductModelCode)
            );
        }

        $variantProduct = $this->createVariantProduct->from($product, $productModel);
        $this->turnProduct->into($variantProduct);

        return $variantProduct;
    }
}
