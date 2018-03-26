<?php

namespace Pim\Component\Connector\Writer\Database;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Product writer
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    /** @var VersionManager */
    protected $versionManager;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /**
     * Constructor
     *
     * @param VersionManager                $versionManager
     * @param BulkSaverInterface            $productSaver
     * @param EntityManagerClearerInterface $cacheClearer
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->versionManager = $versionManager;
        $this->productSaver = $productSaver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->incrementCount($item);
        }

        $this->productSaver->saveAll($items);
        $this->cacheClearer->clear();
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
    public function initialize()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
    }

    /**
     * @param ProductInterface $product
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($product->getId()) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
