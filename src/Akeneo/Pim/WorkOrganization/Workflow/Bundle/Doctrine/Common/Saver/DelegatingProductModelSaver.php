<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Delegating product model saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DelegatingProductModelSaver implements SaverInterface, BulkSaverInterface
{
    /** @var SaverInterface */
    private $productModelSaver;

    /** @var SaverInterface */
    private $productModelDraftSaver;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityWithValuesDraftBuilderInterface */
    private $draftBuilder;

    /** @var RemoverInterface */
    private $productDraftRemover;

    /** @var NotGrantedDataMergerInterface */
    private $mergeDataOnProductModel;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productModelDraftRepository;

    /** @var ObjectManager */
    private $objectManager;

    /** @var BulkSaverInterface */
    private $bulkProductModelSaver;

    /** @var PimUserDraftSourceFactory */
    private $draftSourceFactory;

    public function __construct(
        ObjectManager $objectManager,
        SaverInterface $productModelSaver,
        SaverInterface $productModelDraftSaver,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProductModel,
        ProductModelRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        BulkSaverInterface $bulkProductModelSaver,
        PimUserDraftSourceFactory $draftSourceFactory
    ) {
        $this->objectManager = $objectManager;
        $this->productModelSaver = $productModelSaver;
        $this->productModelDraftSaver = $productModelDraftSaver;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->draftBuilder = $draftBuilder;
        $this->productDraftRemover = $productDraftRemover;
        $this->mergeDataOnProductModel = $mergeDataOnProductModel;
        $this->productModelRepository = $productModelRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->bulkProductModelSaver = $bulkProductModelSaver;
        $this->draftSourceFactory = $draftSourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save($productModel, array $options = []): void
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected($productModel, ProductModelInterface::class);
        }

        if ($this->isOwner($productModel) || null === $productModel->getId()) {
            $this->productModelSaver->save($productModel, $options);
        } elseif ($this->canEdit($productModel)) {
            $this->saveProductModelDraft($productModel, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $productModels, array $options = []): void
    {
        if (empty($productModels)) {
            return;
        }

        $productModelsToCompute = [];
        foreach ($productModels as $productModel) {
            $this->validateObject($productModel, ProductModelInterface::class);

            if ($this->isOwner($productModel) || null === $productModel->getId()) {
                $productModelsToCompute[] = $productModel;
            } elseif ($this->canEdit($productModel)) {
                $this->saveProductModelDraft($productModel, $options);
            }
        }

        $this->bulkProductModelSaver->saveAll($productModelsToCompute, $options);
    }

    /**
     * Is user owner of the product model?
     *
     * @param ProductModelInterface $productModel
     *
     * @return bool
     */
    private function isOwner(ProductModelInterface $productModel): bool
    {
        return $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);
    }

    /**
     * Can user edit the product model?
     *
     * @param ProductModelInterface $productModel
     *
     * @return bool
     */
    private function canEdit(ProductModelInterface $productModel): bool
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);
    }

    /**
     * @param ProductModelInterface $filteredProductModel
     * @param array                 $options
     */
    private function saveProductModelDraft(ProductModelInterface $filteredProductModel, array $options): void
    {
        $fullProductModel = $this->getFullProductModel($filteredProductModel);
        $username = $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
        $productModelDraft = $this->draftBuilder->build(
            $fullProductModel,
            $this->draftSourceFactory->createFromUser($this->tokenStorage->getToken()->getUser())
        );

        if (null !== $productModelDraft) {
            $this->productModelDraftSaver->save($productModelDraft, $options);
            $this->objectManager->refresh($fullProductModel);
        } elseif (null !== $draft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($fullProductModel, $username)) {
            $this->productDraftRemover->remove($draft);
        }
    }

    /**
     * $filteredProductModel is the product model with only granted data.
     * In order to build the draft we have to get the full product model with all data (included not granted).
     * To do that, we get the product model from the DB and merge new data from $filteredProductModel into this product model.
     *
     * @param ProductModelInterface $filteredProductModel
     *
     * @return ProductModelInterface
     */
    private function getFullProductModel(ProductModelInterface $filteredProductModel): ProductModelInterface
    {
        if (null === $filteredProductModel->getId()) {
            return $filteredProductModel;
        }

        $fullProductModel = $this->productModelRepository->findOneByIdentifier($filteredProductModel->getCode());

        return $this->mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel);
    }

    /**
     * Raises an exception when we try to save another object than expected
     *
     * @param object $object
     * @param string $expectedClass
     *
     * @throws \InvalidArgumentException
     */
    private function validateObject($object, $expectedClass): void
    {
        if (!$object instanceof $expectedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    $expectedClass,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
