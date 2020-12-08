<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvent;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\UnpublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Published product manager
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductManager
{
    protected ProductRepositoryInterface $productRepository;
    protected AttributeRepositoryInterface $attributeRepository;
    protected PublishedProductRepositoryInterface $publishedRepositoryWithPermission;
    protected EventDispatcherInterface $eventDispatcher;
    protected PublisherInterface $publisher;
    protected UnpublisherInterface $unpublisher;
    protected ObjectManager $objectManager;
    protected SaverInterface $publishedProductSaver;
    protected BulkSaverInterface $publishedProductBulkSaver;
    protected RemoverInterface $remover;
    private BulkRemoverInterface $bulkRemover;
    private PublishedProductRepositoryInterface $publishedRepositoryWithoutPermission;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        PublishedProductRepositoryInterface $publishedRepositoryWithPermission,
        AttributeRepositoryInterface $attributeRepository,
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher,
        ObjectManager $objectManager,
        SaverInterface $publishedProductSaver,
        BulkSaverInterface $publishedProductBulkSaver,
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
        $this->publishedProductBulkSaver = $publishedProductBulkSaver;
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
            $this->publishedProductSaver->save($published, ['add_default_values' => false]);
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

        $this->publishedProductBulkSaver->saveAll($publishedProducts, ['add_default_values' => false]);

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
            $this->dispatchEvent(PublishedProductEvents::POST_UNPUBLISH, $product, $publishedProductWithoutPermission);

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

        $this->publishedProductBulkSaver->saveAll($publishedProducts, ['add_default_values' => false]);
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
