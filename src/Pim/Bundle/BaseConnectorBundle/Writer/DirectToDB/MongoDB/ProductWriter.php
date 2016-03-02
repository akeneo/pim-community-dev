<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductNormalizer;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\PendingMassPersister;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product writer using direct MongoDB method in order to
 * achieve better performances.
 *
 * WARNING: this writer only handles insertion of full products (no update).
 * So it's mainly suitable for initial one-shot import
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Will be removed in 1.6
 */
class ProductWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * This event is thrown before converting products to documents, so
     * products can still be modified before insertion.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_INSERT = 'pim_base_connector.direct_to_db_writer.pre_insert';

    /**
     * This event is thrown before converting products to documents, so
     * products can still be modified before update.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_UPDATE = 'pim_base_connector.direct_to_db_writer.pre_update';

    /**
     * This event is thrown after insertion of products to database
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_INSERT = 'pim_base_connector.direct_to_db_writer.post_insert';

    /**
     * This event is thrown after update of products to database
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_UPDATE = 'pim_base_connector.direct_to_db_writer.post_update';

    /** @var DocumentManager */
    protected $documentManager;

    /**@var PendingMassPersister */
    protected $pendingPersister;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var MongoObjectsFactory */
    protected $mongoFactory;

    /** @var string */
    protected $productClass;

    /** @var Collection */
    protected $collection;

    /** @var CacheClearer */
    protected $cacheClearer;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param DocumentManager          $documentManager
     * @param PendingMassPersister     $pendingPersister
     * @param NormalizerInterface      $normalizer
     * @param EventDispatcherInterface $eventDispatcher
     * @param MongoObjectsFactory      $mongoFactory
     * @param string                   $productClass
     * @param CacheClearer             $cacheClearer
     */
    public function __construct(
        DocumentManager $documentManager,
        PendingMassPersister $pendingPersister,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher,
        MongoObjectsFactory $mongoFactory,
        $productClass,
        CacheClearer $cacheClearer
    ) {
        $this->documentManager  = $documentManager;
        $this->pendingPersister = $pendingPersister;
        $this->normalizer       = $normalizer;
        $this->eventDispatcher  = $eventDispatcher;
        $this->mongoFactory     = $mongoFactory;
        $this->productClass     = $productClass;
        $this->cacheClearer     = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        $this->collection = $this->documentManager->getDocumentCollection($this->productClass);

        $productsToInsert = [];
        $productsToUpdate = [];
        foreach ($products as $product) {
            if (null === $product->getId()) {
                $productsToInsert[] = $product;
                $product->setId($this->mongoFactory->createMongoId());
            } else {
                $productsToUpdate[] = $product;
            }
        }

        $this->eventDispatcher->dispatch(self::PRE_INSERT, new GenericEvent($productsToInsert));
        $this->eventDispatcher->dispatch(self::PRE_UPDATE, new GenericEvent($productsToUpdate));
        $insertDocs = $this->getDocsFromProducts($productsToInsert);
        $updateDocs = $this->getDocsFromProducts($productsToUpdate);

        if (count($insertDocs) > 0) {
            $this->insertDocuments($insertDocs);
        }

        if (count($updateDocs) > 0) {
            $this->updateDocuments($updateDocs);
        }

        $this->pendingPersister->persistPendingVersions($products);

        $this->eventDispatcher->dispatch(self::POST_INSERT, new GenericEvent($productsToInsert));
        $this->eventDispatcher->dispatch(self::POST_UPDATE, new GenericEvent($productsToUpdate));
        $this->documentManager->clear();
        $this->cacheClearer->clear();
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
        $context = [ProductNormalizer::MONGO_COLLECTION_NAME => $this->collection->getName()];

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
    protected function insertDocuments($docs)
    {
        $this->collection->batchInsert($docs);
        $productsCount = count($docs);
        for ($i = 0; $i < $productsCount; $i++) {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }

    /**
     * Apply update from the provided products documents into MongoDB
     *
     * @param array $docs
     */
    protected function updateDocuments($docs)
    {
        foreach ($docs as $doc) {
            $criteria = [
                '_id' => $doc['_id']
            ];
            $this->collection->update($criteria, $doc);
            $this->stepExecution->incrementSummaryInfo('process');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->cacheClearer->clear(true);
    }
}
