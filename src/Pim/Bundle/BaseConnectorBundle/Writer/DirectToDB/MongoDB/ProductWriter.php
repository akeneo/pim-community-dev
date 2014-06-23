<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\TransformBundle\Transform\MongoDB\ProductTransformer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

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
     * @param ProductTransformer $productTransformer
     * @param ProductManager     $productManager
     * @param DocumentManager    $documentManager
     */
    public function __construct(
        ProductTransformer $productTransformer,
        ProductManager $productManager,
        DocumentManager $objectManager)
    {
        $this->productTransformer = $productTransformer;
        $this->productManager = $productManager;
        $this->documentManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->productManager->handleAllMedia($items);
        $mongoCollection = $this->documentManager->getDocumentCollection(get_class(reset($items)));

        $transformContext = array();
        $transformContext[ProductNormalizer::MONGO_COLLECTION_NAME] = $mongoCollection->getName();

        $mongoItems = array();
        foreach ($items as $product) {
            $mongoItem = $this->productTransformer->transform($product, $transformContext);
            $mongoItems[] = $mongoItem;
        }

        $mongoCollection->batchInsert($mongoItems);

        foreach ($items as $item) {
            $this->incrementCount($item);
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
