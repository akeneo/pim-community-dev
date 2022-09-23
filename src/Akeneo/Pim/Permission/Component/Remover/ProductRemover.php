<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Remove a product if the user is authorized to do so.
 *
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class ProductRemover implements RemoverInterface, BulkRemoverInterface
{
    public function __construct(
        protected RemoverInterface $remover,
        protected BulkRemoverInterface $bulkRemover,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function remove($filteredProduct, array $options = []): void
    {
        $this->ensureIsAProduct($filteredProduct);
        $this->checkUserAuthorization($filteredProduct);

        // As $filteredProduct is a product filtered with only granted data and is unknown by doctrine,
        // we have to find the full product to be able to remove it.
        $fullProduct = $this->productRepository->find($filteredProduct->getUuid());

        $this->remover->remove($fullProduct, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $filteredProducts, array $options = []): void
    {
        $fullProducts = [];
        foreach ($filteredProducts as $filteredProduct) {
            $this->ensureIsAProduct($filteredProduct);
            $this->checkUserAuthorization($filteredProduct);

            // As $filteredProduct is a product filtered with only granted data and is unknown by doctrine,
            // we have to find the full product to be able to remove it.
            $fullProducts[] = $this->productRepository->find($filteredProduct->getUuid());
        }

        $this->bulkRemover->removeAll($fullProducts, $options);
    }

    /**
     * @param mixed $filteredProduct
     *
     * @throws InvalidObjectException If the parameter is not a instance of ProductInterface.
     */
    protected function ensureIsAProduct($filteredProduct): void
    {
        if (!$filteredProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($filteredProduct),
                ProductInterface::class
            );
        }
    }

    /**
     * @param ProductInterface $filteredProduct
     *
     * @throws ResourceAccessDeniedException If the user is not owner on the product.
     */
    protected function checkUserAuthorization(ProductInterface $filteredProduct): void
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $filteredProduct);

        if (!$isOwner) {
            throw new ResourceAccessDeniedException(
                $filteredProduct,
                'You can delete a product only if it is classified in at least one category on which you have an own permission.'
            );
        }
    }
}
