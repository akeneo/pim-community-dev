<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Saver;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Store product through product drafts
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var ChangesCollector */
    protected $collector;

    /** @var ChangeSetComputerInterface */
    protected $changeSet;

    /** @var string */
    protected $storageDriver;

    /**
     * @param ObjectManager                   $om
     * @param CompletenessManager             $completenessManager
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param EventDispatcherInterface        $dispatcher
     * @param ChangesCollector                $collector
     * @param ChangeSetComputerInterface      $changeSet
     * @param string                          $storageDriver
     */
    public function __construct(
        ObjectManager $om,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet,
        $storageDriver
    ) {
        $this->objectManager = $om;
        $this->completenessManager = $completenessManager;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->collector = $collector;
        $this->changeSet = $changeSet;
        $this->storageDriver = $storageDriver;
    }

    /**
     * TODO: do not check the context here. ProductDraftSaver should only persist drafts.
     *
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "%s" provided',
                    ClassUtils::getClass($product)
                )
            );
        }

        $options = $this->resolveOptions($options);

        if (null === $product->getId()) {
            $isOwner = true;
        } else {
            try {
                $isOwner = $this->securityContext->isGranted(Attributes::OWN, $product);
            } catch (AuthenticationCredentialsNotFoundException $e) {
                // We are probably on a CLI context
                $isOwner = true;
            }
        }

        if ($isOwner || $options['bypass_product_draft'] || !$this->objectManager->contains($product)) {
            $this->persistProduct($product, $options);
        } else {
            $this->refreshProductValues($product);
            $this->persistProductDraft($product);
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

        $allOptions = $this->resolveOptions($options);
        $itemOptions = $allOptions;
        $itemOptions['flush'] = false;

        foreach ($products as $product) {
            $this->save($product, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Persist the product
     *
     * @param ProductInterface $product
     * @param array            $options
     */
    protected function persistProduct(ProductInterface $product, array $options)
    {
        // TODO : remove the flush case, once the saveAll will be implemented
        $this->objectManager->persist($product);

        if (true === $options['schedule'] || true === $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if (true === $options['recalculate'] || true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (true === $options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
                'bypass_product_draft' => false
            ]
        );
        $resolver->setAllowedTypes(
            [
                'recalculate' => 'bool',
                'flush' => 'bool',
                'schedule' => 'bool',
                'bypass_product_draft' => 'bool'
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * Persist a product draft of the product
     *
     * @param ProductInterface $product
     *
     * @return null
     *
     * @throws \LogicException
     */
    protected function persistProductDraft(ProductInterface $product)
    {
        if (null === $submittedData = $this->collector->getData()) {
            throw new \LogicException('No product data were collected');
        }

        $username = $this->getUser()->getUsername();
        if (null === $productDraft = $this->repository->findUserProductDraft($product, $username)) {
            $productDraft = $this->factory->createProductDraft($product, $username);
            $this->objectManager->persist($productDraft);
        }

        $event = $this->dispatcher->dispatch(
            ProductDraftEvents::PRE_UPDATE,
            new ProductDraftEvent(
                $productDraft,
                $this->changeSet->compute($product, $submittedData)
            )
        );
        $changes = $event->getChanges();

        if (empty($changes)) {
            $this->objectManager->remove($productDraft);

            return $this->objectManager->flush();
        }

        $productDraft->setChanges($changes);

        $this->objectManager->flush();
    }

    /**
     * Get user from the security context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws \LogicException
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            throw new \LogicException('No user logged in');
        }

        if (!is_object($user = $token->getUser())) {
            throw new \LogicException('No user logged in');
        }

        return $user;
    }

    /**
     * Refresh the values of the product to not have the changes made by binding the request to the form
     * This is hackish, but no elegant solution has been found
     *
     * @param ProductInterface $product
     */
    protected function refreshProductValues(ProductInterface $product)
    {
        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM === $this->storageDriver) {
            foreach ($product->getValues() as $value) {
                if (true === $this->objectManager->contains($value)) {
                    $this->objectManager->refresh($value);
                    foreach ($value->getPrices() as $price) {
                        if (true === $this->objectManager->contains($price)) {
                            $this->objectManager->refresh($price);
                        }
                    }
                } else {
                    $value->setData(null);
                }
            }
        } else {
            // because of Mongo (values are embedded) we'll have to refresh the whole product instead
            // of refreshing only the values
            // so we'll have to store all other data that could have changed

            $enabled = $product->isEnabled();
            $categories = $product->getCategories();
            $associations = $product->getAssociations();

            $this->objectManager->refresh($product);

            $product->setEnabled($enabled);
            $product->getCategories()->clear();
            foreach ($categories as $category) {
                $product->addCategory($category);
            }
            $product->setAssociations($associations->toArray());
        }
    }
}
