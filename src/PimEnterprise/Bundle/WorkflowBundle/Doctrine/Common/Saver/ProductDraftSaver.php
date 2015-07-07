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

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Save product drafts, drafts will need to be approved to be merged in the working product data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ProductSavingOptionsResolver */
    protected $optionsResolver;

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

    /** @var MetricFactory */
    protected $metricFactory;

    /** @var MediaFactory */
    protected $mediaFactory;

    /**
     * @param ObjectManager                   $objectManager
     * @param ProductSavingOptionsResolver    $optionsResolver
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param EventDispatcherInterface        $dispatcher
     * @param ChangesCollector                $collector
     * @param ChangeSetComputerInterface      $changeSet
     * @param string                          $storageDriver
     */
    public function __construct(
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet,
        $storageDriver,
        MetricFactory $metricFactory,
        MediaFactory $mediaFactory
    ) {
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->collector = $collector;
        $this->changeSet = $changeSet;
        $this->storageDriver = $storageDriver;
        $this->metricFactory = $metricFactory;
        $this->mediaFactory = $mediaFactory;
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

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->refreshProductValues($product);
        $this->persistProductDraft($product, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $allOptions = $this->optionsResolver->resolveSaveAllOptions($options);
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
     * Persist a product draft of the product
     *
     * @param ProductInterface $product
     * @param array            $options
     *
     * @return null
     *
     * @throws \LogicException
     */
    protected function persistProductDraft(ProductInterface $product, array $options)
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
                    $this->eraseValueData($value);
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

    /**
     * Handle each kind of attributes $value, to set to a null equivalent
     * This is hackish, but no elegant solution has been found
     *
     * @param AbstractProductValue $value
     */
    protected function eraseValueData(AbstractProductValue $value)
    {
        $attributeType = $value->getAttribute()->getAttributeType();

        switch ($attributeType) {
            case 'pim_catalog_simpleselect':
                $value->setOption();
                break;

            case 'pim_catalog_multiselect':
            case 'pim_catalog_price_collection':
                $value->setPrices(new ArrayCollection());
                break;

            case 'pim_catalog_metric':
                $value->setMetric($this->metricFactory->createMetric(''));
                break;

            case 'pim_catalog_image':
            case 'pim_catalog_file':
                $value->setMedia($this->mediaFactory->createMedia());
                break;

            default:
                $value->setData(null);
                break;
        }
    }
}
