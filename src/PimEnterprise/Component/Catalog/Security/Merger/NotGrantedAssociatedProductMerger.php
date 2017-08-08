<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Merge not granted associations with new associations. Example:
 * In database, your product "my_product" contains those associations:
 * {
 *    "associations": {
 *      "products": ["product_a", "product_b", "product_c"]
 *    }
 * }
 *
 * But "product_a" is not viewable by the connected user.
 * That's means when he will get the product "my_product", the application will return:
 * {
 *    "associations": {
 *      "products": ["product_b", "product_c"]
 *    }
 * }
 * (@see \PimEnterprise\Component\Catalog\Security\Filter\NotGrantedAssociatedProductFilter)
 *
 * When user will update "my_product":
 * {
 *    "associations": {
 *      "products": ["product_c"]
 *    }
 * }
 * we have to merge not granted data (here "product_a") before saving data in database.
 *
 * Finally, "my_product" will contain:
 * {
 *    "associations": {
 *      "products": ["product_a", "product_c"]
 *    }
 * }
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedAssociatedProductMerger implements NotGrantedDataMergerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ProductRepositoryInterface    $productRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($product): void
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($product), ProductInterface::class);
        }

        $associatedProducts = $this->productRepository->getAssociatedProductIds($product);
        foreach ($associatedProducts as $associatedProductData) {
            $associatedProduct = $this->productRepository->findOneByIdentifier($associatedProductData['product_identifier']);
            if (null !== $associatedProduct && !$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
                $product->getAssociationForTypeCode($associatedProductData['association_type_code'])->addProduct($associatedProduct);
            }
        }
    }
}
