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
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Remove a product model if the user is authorized to do so.
 */
class ProductModelRemover implements RemoverInterface, BulkRemoverInterface
{
    /** @var RemoverInterface */
    protected $remover;

    /** @var BulkRemoverInterface */
    protected $bulkRemover;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /**
     * @param RemoverInterface                      $remover
     * @param BulkRemoverInterface                  $bulkRemover
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     */
    public function __construct(
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->remover = $remover;
        $this->bulkRemover = $bulkRemover;
        $this->authorizationChecker = $authorizationChecker;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($filteredProductModel, array $options = []): void
    {
        $this->ensureIsAProductModel($filteredProductModel);
        $this->checkUserAuthorization($filteredProductModel);

        // As $filteredProductModel is a productModel filtered with only granted data and is unknown by doctrine,
        // we have to find the full productModel to be able to remove it.
        $fullProductModel = $this->productModelRepository->findOneByIdentifier($filteredProductModel->getCode());

        $this->remover->remove($fullProductModel, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $filteredProductModels, array $options = []): void
    {
        $fullProductModels = [];
        foreach ($filteredProductModels as $filteredProductModel) {
            $this->ensureIsAProductModel($filteredProductModel);
            $this->checkUserAuthorization($filteredProductModel);

            // As $filteredProductModel is a productModel filtered with only granted data and is unknown by doctrine,
            // we have to find the full productModel to be able to remove it.
            $fullProductModels[] = $this->productModelRepository->findOneByIdentifier($filteredProductModel->getCode());
        }

        $this->bulkRemover->removeAll($fullProductModels, $options);
    }

    /**
     * @param mixed $filteredProductModel
     *
     * @throws InvalidObjectException If the parameter is not a instance of ProductModelInterface.
     */
    protected function ensureIsAProductModel($filteredProductModel): void
    {
        if (!$filteredProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($filteredProductModel),
                ProductModelInterface::class
            );
        }
    }

    /**
     * @param ProductModelInterface $filteredProductModel
     *
     * @throws ResourceAccessDeniedException If the user is not owner on the productModel.
     */
    protected function checkUserAuthorization(ProductModelInterface $filteredProductModel): void
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel);

        if (!$isOwner) {
            throw new ResourceAccessDeniedException(
                $filteredProductModel,
                'You can delete a product model only if it is classified in at least one category on which you have an own permission.'
            );
        }
    }
}
