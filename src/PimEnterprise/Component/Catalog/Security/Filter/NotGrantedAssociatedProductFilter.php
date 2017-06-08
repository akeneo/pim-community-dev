<?php

namespace PimEnterprise\Component\Catalog\Security\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedHttpException;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter not granted associated product from product
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotGrantedAssociatedProductFilter implements NotGrantedDataFilterInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($product)
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($product), ProductInterface::class);
        }

        $associatedProductIds = $this->productRepository->getAssociatedProductIds($product);

        foreach ($product->getAssociations() as $association) {
            foreach ($associatedProductIds as $associatedProductId) {
                if ($associatedProductId['association_id'] === $association->getId()) {
                    try {
                        $this->productRepository->find($associatedProductId['product_id']);
                    } catch (ResourceAccessDeniedHttpException $e) {
                        $association->removeProduct($e->getResource());
                    }
                }
            }
        }

        return $product;
    }
}
