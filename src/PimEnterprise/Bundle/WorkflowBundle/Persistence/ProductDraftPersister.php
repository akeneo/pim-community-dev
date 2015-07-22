<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;

/**
 * Store product through product drafts
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftPersister implements ProductPersister
{
    /** @var ManagerRegistry */
    protected $registry;

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

    /** @var MetricFactory */
    protected $metricFactory;

    /** @var MediaFactory */
    protected $mediaFactory;

    /**
     * @param ManagerRegistry                 $registry
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
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet,
        $storageDriver,
        MetricFactory $metricFactory = null,
        MediaFactory $mediaFactory = null
    ) {
        $this->registry = $registry;
        $this->completenessManager = $completenessManager;
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
     * TODO: do not check the context here. ProductDraftPersister should only persist propostions.
     *
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        $options = array_merge(['bypass_product_draft' => false], $options);

        $manager = $this->registry->getManagerForClass(get_class($product));

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

        if ($isOwner || $options['bypass_product_draft'] || !$manager->contains($product)) {
            $this->persistProduct($manager, $product, $options);
        } else {
            $this->refreshProductValues($manager, $product);
            $this->persistProductDraft($manager, $product);
        }
    }

    /**
     * Persist the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     * @param array            $options
     */
    protected function persistProduct(ObjectManager $manager, ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush'       => true,
                'schedule'    => true,
            ],
            $options
        );

        $manager->persist($product);

        if ($options['schedule'] || $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if ($options['recalculate'] || $options['flush']) {
            $manager->flush();
        }

        if ($options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }

    /**
     * Persist a product draft of the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     *
     * @throws \LogicException
     *
     * @return null
     */
    protected function persistProductDraft(ObjectManager $manager, ProductInterface $product)
    {
        if (null === $submittedData = $this->collector->getData()) {
            throw new \LogicException('No product data were collected');
        }

        $username = $this->getUser()->getUsername();
        $locale = $product->getLocale();
        if (null === $productDraft = $this->repository->findUserProductDraft($product, $username, $locale)) {
            $productDraft = $this->factory->createProductDraft($product, $username, $locale);
            $manager->persist($productDraft);
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
            $manager->remove($productDraft);

            return $manager->flush();
        }

        $productDraft->setChanges($changes);

        $manager->flush();
    }

    /**
     * Get user from the security context
     *
     * @throws \LogicException
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
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
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     */
    protected function refreshProductValues(ObjectManager $manager, ProductInterface $product)
    {
        if (PimCatalogExtension::DOCTRINE_ORM === $this->storageDriver) {
            foreach ($product->getValues() as $value) {
                if (true === $manager->contains($value)) {
                    $manager->refresh($value);
                    foreach ($value->getPrices() as $price) {
                        if (true === $manager->contains($price)) {
                            $manager->refresh($price);
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

            $manager->refresh($product);

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
                if ($this->metricFactory) {
                    $value->setMetric($this->metricFactory->createMetric(''));
                } else {
                    $value->setMetric(new Metric());
                }
                break;

            case 'pim_catalog_image':
            case 'pim_catalog_file':
                if ($this->mediaFactory) {
                    $value->setMedia($this->mediaFactory->createMedia());
                } else {
                    $value->setMedia(new ProductMedia());
                }
                break;

            default:
                $value->setData(null);
                break;
        }
    }
}
