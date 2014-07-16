<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use Pim\Bundle\VersioningBundle\Doctrine\ORM\PendingVersionMassPersister;

use Pim\Bundle\TransformBundle\Normalizer\MongoDB\ProductNormalizer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Doctrine\ODM\MongoDB\DocumentManager;

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
    /** @var ProductManager */
     protected $productManager;

    /** @var DocumentManager */
     protected $documentManager;

    /**@var PendingVersionMassPersister */
     protected $pendingPersister;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var Collection */
    protected $collection;

    /**
     * @param ProductManager              $productManager
     * @param DocumentManager             $documentManager
     * @param PendingVersionMassPersister $pendingPersister
     * @param NormalizerInterface         $normalizer
     */
    public function __construct(
        ProductManager $productManager,
        DocumentManager $documentManager,
        PendingVersionMassPersister $pendingPersister,
        NormalizerInterface $normalizer
    ) {
        $this->productManager   = $productManager;
        $this->documentManager  = $documentManager;
        $this->pendingPersister = $pendingPersister;
        $this->normalizer       = $normalizer;
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

        $this->pendingPersister->persistPendingVersions($products);
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
    protected function getDocsFromProducts(array $products)
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
        }

        return [$insertDocs, $updateDocs];
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
            $this->stepExecution->incrementSummaryInfo('created');
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
            $this->stepExecution->incrementSummaryInfo('updated');
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
        return array();
    }
}
