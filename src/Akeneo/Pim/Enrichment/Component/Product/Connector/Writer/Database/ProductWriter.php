<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

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

    /**
     * @param VersionManager $versionManager
     * @param BulkSaverInterface $productSaver
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver
    ) {
        $this->versionManager = $versionManager;
        $this->productSaver = $productSaver;
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
