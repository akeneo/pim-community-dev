<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvent;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvents;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use PimEnterprise\Component\Workflow\Publisher\UnpublisherInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Published product manager
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductManager
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var PublishedProductRepositoryInterface*/
    protected $publishedRepositoryWithPermission;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var PublisherInterface */
    protected $publisher;

    /** @var  UnpublisherInterface */
    protected $unpublisher;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var SaverInterface */
    protected $publishedProductSaver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var BulkRemoverInterface */
    private $bulkRemover;

    /** @var PublishedProductRepositoryInterface */
    private $publishedRepositoryWithoutPermission;

    /**
     * @param ProductRepositoryInterface          $productRepository     the product repository
     * @param PublishedProductRepositoryInterface $publishedRepositoryWithPermission
     * @param AttributeRepositoryInterface        $attributeRepository   the attribute repository
     * @param EventDispatcherInterface            $eventDispatcher       the event dispatcher
     * @param PublisherInterface                  $publisher             the product publisher
     * @param UnpublisherInterface                $unpublisher           the product unpublisher
     * @param ObjectManager                       $objectManager         the object manager
     * @param SaverInterface                      $publishedProductSaver the object saver
     * @param RemoverInterface                    $remover
     * @param BulkRemoverInterface                $bulkRemover
     * @param PublishedProductRepositoryInterface $publishedRepositoryWithoutPermission
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PublishedProductRepositoryInterface $publishedRepositoryWithPermission,
        AttributeRepositoryInterface $attributeRepository,
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher,
        ObjectManager $objectManager,
        SaverInterface $publishedProductSaver,
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        PublishedProductRepositoryInterface $publishedRepositoryWithoutPermission
    ) {
        $this->productRepository = $productRepository;
        $this->publishedRepositoryWithPermission = $publishedRepositoryWithPermission;
        $this->attributeRepository = $attributeRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->publisher = $publisher;
        $this->unpublisher = $unpublisher;
        $this->objectManager = $objectManager;
        $this->publishedProductSaver = $publishedProductSaver;
        $this->remover = $remover;
        $this->bulkRemover = $bulkRemover;
        $this->publishedRepositoryWithoutPermission = $publishedRepositoryWithoutPermission;
    }


    /**
     * Find the published product
     *
     * @param mixed $publishedId
     *
     * @return PublishedProductInterface
     */
    public function findPublishedProductById($publishedId)
    {
        return $this->publishedRepositoryWithPermission->find($publishedId);
    }

    /**
     * Find the published product by its original product
     *
     * @param mixed $productId
     *
     * @return PublishedProductInterface
     */
    public function findPublishedProductByOriginalId($productId)
    {
        return $this->publishedRepositoryWithPermission->findOneByOriginalProductId($productId);
    }

    /**
     * Find the published product by its original id
     *
     * @param ProductInterface $product
     *
     * @return PublishedProductInterface
     */
    public function findPublishedProductByOriginal(ProductInterface $product)
    {
        return $this->publishedRepositoryWithPermission->findOneByOriginalProductId($product);
    }

    /**
     * Find the working copy, the original product
     *
     * @param mixed $productId
     *
     * @return ProductInterface
     */
    public function findOriginalProduct($productId)
    {
        return $this->productRepository->find($productId);
    }

    /**
     * Find a published product by its identifier
     *
     * @param string $identifier
     *
     * @return PublishedProductInterface
     */
    public function findByIdentifier($identifier)
    {
        return $this->publishedRepositoryWithPermission->findOneByIdentifier($identifier);
    }

    /**
     * Publish a product
     *
     * @param ProductInterface $product
     * @param array            $publishOptions
     *
     * @return PublishedProductInterface
     */
    public function publish(ProductInterface $product, array $publishOptions = [])
    {
        $originalProduct = $this->findOriginalProduct($product->getId());
        $this->dispatchEvent(PublishedProductEvents::PRE_PUBLISH, $originalProduct);

        $published = $this->publishedRepositoryWithoutPermission->findOneByOriginalProduct($product);
        if ($published) {
            $this->unpublisher->unpublish($published);
            $this->remover->remove($published);
        }

        $published = $this->publisher->publish($originalProduct, $publishOptions);

        $publishOptions = array_merge(['flush' => true], $publishOptions);

        if (true === $publishOptions['flush']) {
            $this->publishedProductSaver->save($published);
            $this->dispatchEvent(PublishedProductEvents::POST_PUBLISH, $originalProduct, $published);
        }

        return $published;
    }

    /**
     * Un publish a product
     *
     * @param PublishedProductInterface $published
     */
    public function unpublish(PublishedProductInterface $published)
    {
        $originalPublished = $this->publishedRepositoryWithoutPermission->find($published->getId());
        $product = $originalPublished->getOriginalProduct();
        $this->dispatchEvent(PublishedProductEvents::PRE_UNPUBLISH, $product, $originalPublished);
        $this->unpublisher->unpublish($originalPublished);
        $this->remover->remove($originalPublished);

        $this->dispatchEvent(PublishedProductEvents::POST_UNPUBLISH, $product);
    }

    /**
     * Bulk publish products
     *
     * @param array $products
     */
    public function publishAll(array $products)
    {
        $publishedProducts = [];
        foreach ($products as $product) {
            $published = $this->publish($product, ['with_associations' => false, 'flush' => false]);
            $publishedProducts[] = $published;
        }

        $this->publishedProductSaver->saveAll($publishedProducts);

        foreach ($publishedProducts as $publishedProduct) {
            $this->dispatchEvent(
                PublishedProductEvents::POST_PUBLISH,
                $publishedProduct->getOriginalProduct(),
                $publishedProduct
            );
        }

        $this->publishAssociations($products);
    }

    /**
     * Bulk unpublish products
     *
     * @param array $publishedProducts
     */
    public function unpublishAll(array $publishedProducts)
    {
        $publishedProductsWithoutPermission = [];
        foreach ($publishedProducts as $published) {
            $publishedProductWithoutPermission = $this->publishedRepositoryWithoutPermission->find($published->getId());
            $product = $publishedProductWithoutPermission->getOriginalProduct();

            $this->dispatchEvent(PublishedProductEvents::PRE_UNPUBLISH, $product, $publishedProductWithoutPermission);
            $this->unpublisher->unpublish($publishedProductWithoutPermission);

            $publishedProductsWithoutPermission[] = $publishedProductWithoutPermission;
        }

        $this->bulkRemover->removeAll($publishedProductsWithoutPermission);
    }

    /**
     * Publish all associations where products appears in owner or owned side
     *
     * For instance,
     *  A1- P1 -> cross-sell -> P2
     *  A2- P3 -> cross-sell -> P4
     *
     * If P1 is passed in $products, association A1 is refreshed
     * If P4 is passed in $products, association A2 is refreshed
     *
     * @param ProductInterface[] $products
     */
    protected function publishAssociations(array $products)
    {
        $publishedProducts = [];

        foreach ($products as $product) {
            $published = $this->publishedRepositoryWithoutPermission->findOneByOriginalProduct($product);
            foreach ($product->getAssociations() as $association) {
                $copiedAssociation = $this->publisher->publish($association, ['published' => $published]);
                $published->addAssociation($copiedAssociation);
            }
            $publishedProducts[] = $published;
        }

        $this->publishedProductSaver->saveAll($publishedProducts);
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Dispatch a published product event
     *
     * @param string                    $name
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function dispatchEvent($name, ProductInterface $product, PublishedProductInterface $published = null)
    {
        $this->eventDispatcher->dispatch($name, new PublishedProductEvent($product, $published));
    }
}
