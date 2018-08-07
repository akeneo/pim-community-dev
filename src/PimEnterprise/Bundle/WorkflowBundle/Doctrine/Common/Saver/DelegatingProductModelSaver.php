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

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use PimEnterprise\Component\Workflow\Builder\EntityWithValuesDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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

    public function __construct(
        ObjectManager $objectManager,
        SaverInterface $productModelSaver,
        SaverInterface $productModelDraftSaver,
        EventDispatcherInterface $eventDispatcher, // TODO: Pull-up: To remove
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProductModel,
        ProductModelRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        BulkSaverInterface $bulkProductModelSaver = null // TODO: pull-up to remove '= null'
    ) {
        $this->productModelSaver = $productModelSaver;
        $this->productModelDraftSaver = $productModelDraftSaver;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->draftBuilder = $draftBuilder;
        $this->productDraftRemover = $productDraftRemover;
        $this->mergeDataOnProductModel = $mergeDataOnProductModel;
        $this->productModelRepository = $productModelRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->objectManager = $objectManager;
        $this->bulkProductModelSaver = $bulkProductModelSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($filteredProductModel, array $options = []): void
    {
        if (!$filteredProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected($filteredProductModel, ProductModelInterface::class);
        }

        $fullProductModel = $this->getFullProductModel($filteredProductModel);

        if ($this->isOwner($fullProductModel) || null === $fullProductModel->getId()) {
            $this->productModelSaver->save($fullProductModel, $options);
        } elseif ($this->canEdit($fullProductModel)) {
            $this->saveProductModelDraft($fullProductModel, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $filteredProductModels, array $options = []): void
    {
        if (empty($filteredProductModels)) {
            return;
        }

        $productModelsToCompute = [];
        $fullProductModels = [];
        foreach ($filteredProductModels as $filteredProductModel) {
            $this->validateObject($filteredProductModel, ProductModelInterface::class);

            $fullProductModel = $this->getFullProductModel($filteredProductModel);
            $fullProductModels[] = $fullProductModel;

            if ($this->isOwner($fullProductModel) || null === $fullProductModel->getId()) {
                $productModelsToCompute[] = $fullProductModel;
            } elseif ($this->canEdit($fullProductModel)) {
                $this->saveProductModelDraft($fullProductModel, $options);
            }
        }

        if (null !== $this->bulkProductModelSaver) {
            $this->bulkProductModelSaver->saveAll($productModelsToCompute, $options);
        }
        $this->objectManager->flush();
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
     * @param ProductModelInterface $fullProductModel
     * @param array                 $options
     */
    private function saveProductModelDraft(ProductModelInterface $fullProductModel, array $options): void
    {
        $username = $this->tokenStorage->getToken()->getUser()->getUsername();
        $productModelDraft = $this->draftBuilder->build($fullProductModel, $username);

        if (null !== $productModelDraft) {
            $this->productModelDraftSaver->save($productModelDraft, $options);
        } elseif (null !== $draft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($fullProductModel, $username)) {
            $this->productDraftRemover->remove($draft);
        }
    }

    /**
     * $filteredProductModel is the product model with only granted data.
     * To avoid to lose data, we have to send to the save the full product model with all data (included not granted).
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
