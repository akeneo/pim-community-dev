<?php

namespace Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Product and product model writer for mass edit
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    /** @var VersionManager */
    protected $versionManager;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var BulkSaverInterface */
    protected $productModelSaver;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /**
     * Constructor
     *
     * @param VersionManager                $versionManager
     * @param BulkSaverInterface            $productSaver
     * @param BulkSaverInterface            $productModelSaver
     * @param EntityManagerClearerInterface $cacheClearer
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->versionManager = $versionManager;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = array_filter($items, function ($item) {
            return $item instanceof ProductInterface;
        });
        $productModels = array_filter($items, function ($item) {
            return $item instanceof ProductModelInterface;
        });

        array_walk($items, function ($item) {
            $this->incrementCount($item);
        });

        $this->productSaver->saveAll($products);
        $this->productModelSaver->saveAll($productModels);
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
     * @param EntityWithFamilyInterface $entity
     */
    protected function incrementCount(EntityWithFamilyInterface $entity)
    {
        if ($entity->getId()) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
