<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Remove a product if the user is authorized to do so.
 *
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class ProductRemover implements RemoverInterface, BulkRemoverInterface
{
    /** @var RemoverInterface */
    protected $remover;

    /** @var BulkRemoverInterface */
    protected $bulkRemover;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /**
     * @param RemoverInterface                      $remover
     * @param BulkRemoverInterface                  $bulkRemover
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $filteredProductRepository
    ) {
        $this->remover = $remover;
        $this->bulkRemover = $bulkRemover;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $filteredProductRepository;
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
        $fullProduct = $this->productRepository->findOneByIdentifier($filteredProduct->getIdentifier());

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
            $fullProducts[] = $this->productRepository->findOneByIdentifier($filteredProduct->getIdentifier());
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
