<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\EventListener\MongoDBODM\AddProductVersionListener;

use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\TransformBundle\Normalizer\MongoDB\ProductNormalizer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Driver\Connection;


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
 */
class ProductWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * @var ProductTransformer
     */
     protected $productTransformer;

    /**
     * @var ProductManager
     */
     protected $productManager;

    /**
     * @var DocumentManager
     */
     protected $documentManager;

    /**
     * @var VersionManager
     */
     protected $versionManager;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Collection
     */
    protected $collection;
    
    /**
     * @param ProductTransformer $productTransformer
     * @param ProductManager     $productManager
     * @param DocumentManager    $documentManager
     */
    public function __construct(
        ProductManager $productManager,
        DocumentManager $documentManager,
        VersionManager $versionManager,
        NormalizerInterface $normalizer,
        Connection $connection
    ) {
        $this->productManager     = $productManager;
        $this->documentManager    = $documentManager;
        $this->versionManager     = $versionManager;
        $this->normalizer         = $normalizer;
        $this->connection         = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        $this->productManager->handleAllMedia($products);

        $this->collection = $this->documentManager->getDocumentCollection(get_class(reset($products)));

        list($insertDocs, $updateDocs) = $this->getDocsFromProducts($products);

        if (count($insertDocs) > 0) {
            $this->insertDocuments($insertDocs);
        }

        if (count($updateDocs) > 0) {
            $this->updateDocuments($updateDocs);
        }

        $this->createPendingVersions($products);
        $this->documentManager->clear();
    }

    /**
     * Normalize products into docs and generate an array
     * with docs to insert and docs to updates
     *
     * @param ProductInterface[] $products
     *
     * @return array
     */
    protected function getDocsFromProducts($products)
    {
        $context = array();
        $context[ProductNormalizer::MONGO_COLLECTION_NAME] = $this->collection->getName();

        $insertDocs = [];
        $updateDocs = [];

        foreach ($products as $product) {
            $doc = $this->normalizer->normalize($product, ProductNormalizer::FORMAT, $context);

            if (null === $product->getId()) {
                $product->setId($doc['_id']);
                $insertDocs[] = $doc;
            } else {
                $updateDocs[] = $doc;
            }
            // TODO: increment write count only for write and update count for update
            $this->incrementCount($product);
        }

        return [$insertDocs, $updateDocs];
    }

    /**
     * Create the pending versions for the products provided
     *
     * @param ProductInterface[] $products
     */
    protected function createPendingVersions(array $products)
    {
        $pendingVersions = [];
        foreach ($products as $product) {
            $pendingVersions[] = $this->addPendingVersion($product);
        }
        if (count($pendingVersions) > 0) {
            $this->batchInsertPendingVersions($pendingVersions);
        }
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
    }

    /**
     * Apply update from the provided products documents into MongoDB
     *
     * @param array $docs
     */
    protected function updateDocuments($docs)
    {
        foreach ($docs as $doc) {
            $criteria = ['_id' => $doc['_id']];
            $this->collection->update($criteria, $doc);
        }
    }

    /**
     * Create the pending version for the product
     *
     * @param ProductInterface $product
     * @return array
     */
    protected function addPendingVersion(ProductInterface $product)
    {
        $now = new \DateTime();

        $version = [];
        $version['author'] = $this->versionManager->getUsername();
        $version['changeset'] = serialize($this->normalizer->normalize($product, 'csv', ['versioning' => true]));
        $version['resource_name'] = get_class($product);
        $version['resource_id'] = $product->getId();
        $version['context'] = $this->versionManager->getContext();
        $version['logged_at'] = $now->format('Y-m-d H:i:s');
        $version['pending'] = true;

        return $version;
    }

    /**
     * Insert into pending versions
     *
     * @param array $pendingVersions
     */
    protected function batchInsertPendingVersions(array $pendingVersions)
    {
        $insert = 'INSERT INTO pim_versioning_version';

        $columns = array_keys($pendingVersions[0]);
        $placeholders = array_fill(0, count($columns), '?');

        $params = [];
        $rawPlaceHolders = [];

        foreach ($pendingVersions as $pendingVersion) {
            $params = array_merge($params, array_values($pendingVersion));

            $rawPlaceholders[] = sprintf('(%s)', implode(',' , $placeholders));
        }
        $values = implode(',', $rawPlaceholders);

        $query = sprintf('%s(%s) VALUES %s', $insert, implode(',', $columns), $values);

        $this->connection->executeQuery($query, $params);
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
        return array();
    }

    /**
     * @param ProductInterface $product
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($product->getId()) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
