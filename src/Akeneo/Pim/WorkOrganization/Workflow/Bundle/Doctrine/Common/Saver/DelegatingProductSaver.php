<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Delegating product saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DelegatingProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var EntityWithValuesDraftBuilderInterface */
    protected $entityWithValuesDraftBuilder;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var RemoverInterface */
    protected $productDraftRemover;

    /** @var NotGrantedDataMergerInterface */
    private $mergeDataOnProduct;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var PimUserDraftSourceFactory */
    private $draftSourceFactory;

    /** @var SaverInterface */
    private $productSaver;

    /** @var BulkSaverInterface */
    private $bulkProductSaver;

    /** @var SaverInterface */
    private $productDraftSaver;

    public function __construct(
        ObjectManager $objectManager,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $entityWithValuesDraftBuilder,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        SaverInterface $productSaver,
        BulkSaverInterface $bulkProductSaver,
        SaverInterface $productDraftSaver
    ) {
        $this->objectManager = $objectManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityWithValuesDraftBuilder = $entityWithValuesDraftBuilder;
        $this->tokenStorage = $tokenStorage;
        $this->productDraftRepo = $productDraftRepo;
        $this->productDraftRemover = $productDraftRemover;
        $this->mergeDataOnProduct = $mergeDataOnProduct;
        $this->productRepository = $productRepository;
        $this->draftSourceFactory = $draftSourceFactory;
        $this->productSaver = $productSaver;
        $this->bulkProductSaver = $bulkProductSaver;
        $this->productDraftSaver = $productDraftSaver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AuthenticationCredentialsNotFoundException if not authenticated
     */
    public function save($product, array $options = [])
    {
        $this->validateObject($product, ProductInterface::class);

        if ($this->isOwner($product) || null === $product->getCreated()) {
            $this->productSaver->save($product, $options);
        } elseif ($this->canEdit($product)) {
            $this->saveProductDraft($product, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $productsToCompute = [];
        foreach ($products as $product) {
            $this->validateObject($product, ProductInterface::class);

            if ($this->isOwner($product) || null === $product->getCreated()) {
                $productsToCompute[] = $product;
            } elseif ($this->canEdit($product)) {
                $this->saveProductDraft($product, $options);
            }
        }

        $this->bulkProductSaver->saveAll($productsToCompute, $options);
    }

    /**
     * Raises an exception when we try to save another object than expected
     *
     * @param object $object
     * @param string $expectedClass
     *
     * @throws \InvalidArgumentException
     */
    protected function validateObject($object, $expectedClass)
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

    /**
     * Is user owner of the product?
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isOwner(ProductInterface $product)
    {
        return $this->authorizationChecker->isGranted(Attributes::OWN, $product);
    }

    /**
     * Can user edit the product?
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function canEdit(ProductInterface $product)
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
    }

    /**
     * @param ProductInterface $filteredProduct
     * @param array $options
     */
    private function saveProductDraft(ProductInterface $filteredProduct, array $options): void
    {
        $fullProduct = $this->getFullProduct($filteredProduct);
        $username = $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
        $productDraft = $this->entityWithValuesDraftBuilder->build(
            $fullProduct,
            $this->draftSourceFactory->createFromUser($this->tokenStorage->getToken()->getUser())
        );

        if (null !== $productDraft) {
            $this->productDraftSaver->save($productDraft, $options);
            $this->objectManager->refresh($fullProduct);
        } elseif (null !== $draft = $this->productDraftRepo->findUserEntityWithValuesDraft($fullProduct, $username)) {
            $this->productDraftRemover->remove($draft);
        }
    }

    /**
     * $filteredProduct is the product with only granted data.
     * In order to build the draft we have to get the full product with all data (included not granted).
     * To do that, we get the product from the DB and merge new data from $filteredProduct into this product.
     *
     * @param ProductInterface $filteredProduct
     *
     * @return ProductInterface
     */
    private function getFullProduct(ProductInterface $filteredProduct): ProductInterface
    {
        if (null === $filteredProduct->getCreated()) {
            return $this->mergeDataOnProduct->merge($filteredProduct);
        }

        $fullProduct = $this->productRepository->find($filteredProduct->getUuid());

        return $this->mergeDataOnProduct->merge($filteredProduct, $fullProduct);
    }
}
