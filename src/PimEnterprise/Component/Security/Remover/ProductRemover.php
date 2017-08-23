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

    /**
     * @param RemoverInterface              $remover
     * @param BulkRemoverInterface          $bulkRemover
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(RemoverInterface $remover, BulkRemoverInterface $bulkRemover, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->remover = $remover;
        $this->bulkRemover = $bulkRemover;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($product, array $options = []): void
    {
        $this->ensureIsAProduct($product);
        $this->checkUserAuthorization($product);

        $this->remover->remove($product, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $products, array $options = []): void
    {
        foreach ($products as $product) {
            $this->ensureIsAProduct($product);
            $this->checkUserAuthorization($product);
        }

        $this->bulkRemover->removeAll($products, $options);
    }

    /**
     * @param mixed $product
     *
     * @throws InvalidObjectException If the parameter is not a instance of ProductInterface.
     */
    protected function ensureIsAProduct($product): void
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                ProductInterface::class
            );
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @throws ResourceAccessDeniedException If the user is not owner on the product.
     */
    protected function checkUserAuthorization(ProductInterface $product): void
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if (!$isOwner) {
            throw new ResourceAccessDeniedException(
                $product,
                'You can delete a product only if it is classified in at least one category on which you have an own permission.'
            );
        }
    }
}
