<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\Versioning\BulkVersionBuilderInterface;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver as BaseProductSaver;
use Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductNormalizer;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Direct To Db Mongo bulk product saver
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSaver extends BaseProductSaver
{
    /** @var BulkVersionBuilderInterface */
    protected $bulkVersionBuilder;

    /**@var BulkSaverInterface */
    protected $versionSaver;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var MongoObjectsFactory */
    protected $mongoFactory;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $databaseName;

    /** @var Collection */
    protected $collection;

    /**
     * {@inheritdoc}
     *
     * @param BulkSaverInterface  $versionSaver
     * @param NormalizerInterface $normalizer
     * @param MongoObjectsFactory $mongoFactory
     * @param string              $productClass
     * @param string              $databaseName
     */
    public function __construct(
        DocumentManager $documentManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        BulkVersionBuilderInterface $bulkVersionBuilder,
        BulkSaverInterface $versionSaver,
        NormalizerInterface $normalizer,
        MongoObjectsFactory $mongoFactory,
        $productClass,
        $databaseName
    ) {
        parent::__construct($documentManager, $completenessManager, $eventDispatcher);

        $this->bulkVersionBuilder = $bulkVersionBuilder;
        $this->versionSaver = $versionSaver;
        $this->normalizer = $normalizer;
        $this->mongoFactory = $mongoFactory;
        $this->productClass = $productClass;
        $this->databaseName = $databaseName;

        $this->collection = $this->entityManager->getDocumentCollection($this->productClass);
    }

    /**
     * {@inheritdoc}
     *
     * Override to do a massive save for the products
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($products, $options));

        $productsToInsert = [];
        $productsToUpdate = [];

        foreach ($products as $product) {
            if (null === $product->getId()) {
                $product->setId($this->mongoFactory->createMongoId());
                $product->setCreated(new \Datetime('now', new \DateTimeZone('UTC')));
                $product->setUpdated(new \Datetime('now', new \DateTimeZone('UTC')));

                $productsToInsert[] = $product;
            } else {
                $product->setUpdated(new \Datetime('now', new \DateTimeZone('UTC')));
                $productsToUpdate[] = $product;
            }

            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));
        }

        $insertDocs = $this->getDocsFromProducts($productsToInsert);
        $updateDocs = $this->getDocsFromProducts($productsToUpdate);

        if (count($insertDocs) > 0) {
            $this->insertDocuments($insertDocs);
        }

        if (count($updateDocs) > 0) {
            $this->updateDocuments($updateDocs);
        }

        foreach ($products as $product) {
            $this->completenessManager->generateMissingForProduct($product);

            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $versions = $this->bulkVersionBuilder->buildVersions($products);
        $this->versionSaver->saveAll($versions);

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($products, $options));
    }

    /**
     * Normalize products into their MongoDB document representation
     *
     * @param ProductInterface[] $products
     *
     * @return array
     */
    protected function getDocsFromProducts(array $products)
    {
        $context = [
            ProductNormalizer::MONGO_COLLECTION_NAME => $this->collection->getName(),
            ProductNormalizer::MONGO_DATABASE_NAME   => $this->databaseName
        ];

        $docs = [];
        foreach ($products as $product) {
            $docs[] = $this->normalizer->normalize($product, ProductNormalizer::FORMAT, $context);
        }

        return $docs;
    }

    /**
     * Insert the provided products documents into MongoDB
     * with the batch insert method
     *
     * @param array $docs
     */
    protected function insertDocuments(array $docs)
    {
        $this->collection->batchInsert($docs);
    }

    /**
     * Apply update from the provided products documents into MongoDB
     *
     * @param array $docs
     */
    protected function updateDocuments(array $docs)
    {
        foreach ($docs as $doc) {
            $id = $doc['_id'];
            $this->collection->update(['_id' => $id], $doc);
        }
    }
}
