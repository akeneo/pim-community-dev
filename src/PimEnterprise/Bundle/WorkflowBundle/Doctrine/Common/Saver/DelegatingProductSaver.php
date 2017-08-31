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
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
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

    /**
     * @param ObjectManager                   $objectManager
     * @param CompletenessManager             $completenessManager
     * @param EventDispatcherInterface        $eventDispatcher
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param ProductDraftBuilderInterface    $productDraftBuilder
     * @param TokenStorageInterface           $tokenStorage
     * @param ProductDraftRepositoryInterface $productDraftRepo
     * @param RemoverInterface                $productDraftRemover
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
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer
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
    }

    /**
     * {@inheritdoc}
     *
     * @throws AuthenticationCredentialsNotFoundException if not authenticated
     */
    public function save($product, array $options = [])
    {
        $this->validateObject($product, 'Pim\Component\Catalog\Model\ProductInterface');
        $hasPermissions = $this->hasPermissions($product);

        if ($hasPermissions) {
            $this->saveProduct($product, $options);
        } else {
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
            $this->validateObject($product, 'Pim\Component\Catalog\Model\ProductInterface');
            $hasPermissions = $this->hasPermissions($product);
            if ($hasPermissions) {
                $productsToCompute[] = $product;
                $this->saveProduct($product, $options, false);
            } else {
                $this->saveProductDraft($product, $options, false);
            }
        }

        $this->objectManager->flush();

        foreach ($productsToCompute as $product) {
            $this->completenessManager->generateMissingForProduct($product);

            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($products, $options));
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
     * Returns true if user is owner of the product or if the product does not exist yet or if the token does not exist
     *
     * @param ProductInterface $product
     *
     * @return bool
     *
     * @throws ResourceAccessDeniedException
     */
    protected function hasPermissions(ProductInterface $product)
    {
        if (null === $product->getId() || null === $this->tokenStorage->getToken()) {
            $hasPermissions = true;
        } else {
            if ($this->authorizationChecker->isGranted(Attributes::VIEW, $product)
                && !$this->authorizationChecker->isGranted(Attributes::EDIT, $product)
                && !$this->authorizationChecker->isGranted(Attributes::OWN, $product)
            ) {
                throw new ResourceAccessDeniedException($product, sprintf(
                    'Product "%s" cannot be updated. It should be at least in an own category.',
                    $product->getIdentifier()
                ));
            }

            $hasPermissions = $this->authorizationChecker->isGranted(Attributes::OWN, $product) && $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
        }

        return $hasPermissions;
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUser()->getUsername();
    }

    /**
     * @param ProductInterface $product
     * @param array            $options
     * @param bool|true        $withFlush
     */
    protected function saveProduct(ProductInterface $product, array $options, $withFlush = true)
    {
        $options['unitary'] = true;
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));

        $this->completenessManager->schedule($product);
        $this->completenessManager->generateMissingForProduct($product);
        $this->uniqueDataSynchronizer->synchronize($product);

        $this->objectManager->persist($product);
        if ($withFlush) {
            $this->objectManager->flush();
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }
    }

    /**
     * @param ProductInterface $product
     * @param array            $options
     * @param bool|true        $withFlush
     */
    protected function saveProductDraft(ProductInterface $product, array $options, $withFlush = true)
    {
        $productDraft = $this->productDraftBuilder->build($product, $this->getUsername());

        if (null !== $productDraft) {
            $this->validateObject($productDraft, 'PimEnterprise\Component\Workflow\Model\ProductDraftInterface');
            $options['unitary'] = true;
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($productDraft, $options));
            $this->objectManager->persist($productDraft);

            if ($withFlush) {
                $this->objectManager->flush();
                $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($productDraft, $options));
                $this->objectManager->refresh($product);
            }
        } elseif (null !== $draft = $this->productDraftRepo->findUserProductDraft($product, $this->getUsername())) {
            $this->productDraftRemover->remove($draft);
        }
    }
}
