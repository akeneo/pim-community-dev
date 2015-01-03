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
use Akeneo\Component\Persistence\BulkSaverInterface;
use Akeneo\Component\Persistence\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Save product drafts, drafts will need to be approved to be merged in the working product data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

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
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet,
        $storageDriver
    ) {
        $this->objectManager = $om;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->collector = $collector;
        $this->changeSet = $changeSet;
        $this->storageDriver = $storageDriver;
    }

    /**
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

        // TODO : resolve options ? with different options than saveAll

        $this->refreshProductValues($product);
        $this->persistProductDraft($product);
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
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        // TODO : extract the resolver part that should be shared by savers
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ]
        );
        $resolver->setAllowedTypes(
            [
                'recalculate' => 'bool',
                'flush' => 'bool',
                'schedule' => 'bool',
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
