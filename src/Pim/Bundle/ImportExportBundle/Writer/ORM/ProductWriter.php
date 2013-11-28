<?php

namespace Pim\Bundle\ImportExportBundle\Writer\ORM;

use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;
use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;

/**
 * Product writer using ORM method
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var AddVersionListener
     */
    protected $addVersionListener;

    /**
     * @var Attribute
     */
    protected $identifierAttribute;

    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Entities which should not be cleared on flush
     *
     * @var array
     */
    protected $nonClearableEntities = array(
        'Oro\\Bundle\\BatchBundle\\Entity\\JobExecution',
        'Oro\\Bundle\\BatchBundle\\Entity\\JobInstance',
        'Pim\\Bundle\\CatalogBundle\\Entity\\ProductAttribute',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Family',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Channel',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Locale',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Currency',
        'Oro\\Bundle\\BatchBundle\\Entity\\StepExecution',
        'Oro\\Bundle\\UserBundle\\Entity\\User',
        'Oro\\Bundle\\OrganizationBundle\\Entity\\BusinessUnit',
        'Pim\\Bundle\\FlexibleEntityBundle\\Entity\\Attribute',
        'Oro\\Bundle\\UserBundle\\Entity\\UserApi'
    );

    /**
     * @param ProductManager     $productManager
     * @param EntityCache        $entityCache
     * @param AddVersionListener $addVersionListener
     */
    public function __construct(
        ProductManager $productManager,
        EntityCache $entityCache,
        AddVersionListener $addVersionListener
    ) {
        $this->productManager     = $productManager;
        $this->entityCache        = $entityCache;
        $this->addVersionListener = $addVersionListener;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->addVersionListener->setRealTimeVersioning(false);
        foreach ($items as $item) {
            $this->incrementCount($item);
        }
        $this->productManager->handleAllMedia($items);
        $this->productManager->saveAll($items, false);

        $storageManager = $this->productManager->getStorageManager();

        foreach ($storageManager->getUnitOfWork()->getIdentityMap() as $className => $entities) {
            if (count($entities) && !in_array($className, $this->nonClearableEntities)) {
                $storageManager->clear($className);
            }
        }
        $this->entityCache->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param ProductInterface $product
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($product->getId()) {
            $this->stepExecution->incrementSummaryInfo('update');
            $this->stepExecution->incrementUpdateCount();
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
            $this->stepExecution->incrementCreationCount();
        }
    }
}
