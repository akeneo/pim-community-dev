<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Catalog\Security\Applier\ApplierInterface;
use PimEnterprise\Component\Catalog\Security\Factory\ApplyDataOnProduct;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ProductDraftBuilderInterface */
    protected $productDraftBuilder;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var RemoverInterface */
    protected $productDraftRemover;

    /** @var ProductUniqueDataSynchronizer */
    private $uniqueDataSynchronizer;

    /** @var ApplierInterface */
    private $applyDataOnProduct;

    /**
     * @param ObjectManager                   $objectManager
     * @param CompletenessManager             $completenessManager
     * @param EventDispatcherInterface        $eventDispatcher
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param ProductDraftBuilderInterface    $productDraftBuilder
     * @param TokenStorageInterface           $tokenStorage
     * @param ProductDraftRepositoryInterface $productDraftRepo
     * @param RemoverInterface                $productDraftRemover
     * @param ProductUniqueDataSynchronizer   $uniqueDataSynchronizer
     * @param ApplierInterface                $applyDataOnProduct
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftBuilderInterface $productDraftBuilder,
        TokenStorageInterface $tokenStorage,
        ProductDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ApplierInterface $applyDataOnProduct
    ) {
        $this->objectManager = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->authorizationChecker = $authorizationChecker;
        $this->productDraftBuilder = $productDraftBuilder;
        $this->tokenStorage = $tokenStorage;
        $this->productDraftRepo = $productDraftRepo;
        $this->productDraftRemover = $productDraftRemover;
        $this->uniqueDataSynchronizer = $uniqueDataSynchronizer;
        $this->applyDataOnProduct = $applyDataOnProduct;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AuthenticationCredentialsNotFoundException if not authenticated
     */
    public function save($filteredProduct, array $options = [])
    {
        $this->validateObject($filteredProduct, ProductInterface::class);

        $fullProduct = $this->applyDataOnProduct->apply($filteredProduct);

        if ($this->isOwner($fullProduct) || null === $fullProduct->getId()) {
            $this->saveProduct($fullProduct, $options);
        } elseif ($this->canEdit($fullProduct)) {
            $this->saveProductDraft($fullProduct, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $filteredProducts, array $options = [])
    {
        if (empty($filteredProducts)) {
            return;
        }

        $productsToCompute = [];
        $fullProducts = [];
        foreach ($filteredProducts as $filteredProduct) {
            $this->validateObject($filteredProduct, ProductInterface::class);
            $fullProduct = $this->applyDataOnProduct->apply($filteredProduct);
            $fullProducts[] = $fullProduct;

            if ($this->isOwner($fullProduct) || null === $fullProduct->getId()) {
                $productsToCompute[] = $fullProduct;
                $this->saveProduct($fullProduct, $options, false);
            } elseif ($this->canEdit($fullProduct)) {
                $this->saveProductDraft($fullProduct, $options, false);
            }
        }

        $this->objectManager->flush();

        foreach ($productsToCompute as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($fullProducts, $options));
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
        return $this->tokenStorage->getToken()->getUser()->getUsername();
    }

    /**
     * @param ProductInterface $fullProduct
     * @param array            $options
     * @param bool|true        $withFlush
     */
    protected function saveProduct(ProductInterface $fullProduct, array $options, $withFlush = true)
    {
        $options['unitary'] = true;
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($fullProduct, $options));

        $this->completenessManager->schedule($fullProduct);
        $this->completenessManager->generateMissingForProduct($fullProduct);
        $this->uniqueDataSynchronizer->synchronize($fullProduct);

        $this->objectManager->persist($fullProduct);
        if ($withFlush) {
            $this->objectManager->flush();
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($fullProduct, $options));
        }
    }

    /**
     * @param ProductInterface $fullProduct
     * @param array            $options
     * @param bool|true        $withFlush
     */
    protected function saveProductDraft(ProductInterface $fullProduct, array $options, $withFlush = true)
    {
        $productDraft = $this->productDraftBuilder->build($fullProduct, $this->getUsername());

        if (null !== $productDraft) {
            $this->validateObject($productDraft, ProductDraftInterface::class);
            $options['unitary'] = true;
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($productDraft, $options));
            $this->objectManager->persist($productDraft);

            if ($withFlush) {
                $this->objectManager->flush();
                $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($productDraft, $options));
                $this->objectManager->refresh($fullProduct);
            }
        } elseif (null !== $draft = $this->productDraftRepo->findUserProductDraft($fullProduct, $this->getUsername())) {
            $this->productDraftRemover->remove($draft);
        }
    }
}
