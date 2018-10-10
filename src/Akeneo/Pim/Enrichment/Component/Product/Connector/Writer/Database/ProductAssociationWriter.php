<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Custom writer for product associations to indicate the number of created/updated association targets
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $bulkSaver;

    /** @var BulkObjectDetacherInterface */
    protected $bulkDetacher;

    /**
     * @param BulkSaverInterface          $bulkSaver
     * @param BulkObjectDetacherInterface $bulkDetacher
     */
    public function __construct(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher
    ) {
        $this->bulkSaver = $bulkSaver;
        $this->bulkDetacher = $bulkDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $objects)
    {
        $this->incrementCount($objects);
        $this->bulkSaver->saveAll($objects);
        $this->bulkDetacher->detachAll($objects);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param array $products
     */
    protected function incrementCount(array $products)
    {
        foreach ($products as $product) {
            foreach ($product->getAssociations() as $association) {
                $count = count($association->getProducts())
                    + count($association->getProductModels())
                    + count($association->getGroups());

                $action = $association->getId() ? 'process' : 'create';

                for ($i = 0; $i < $count; $i++) {
                    $this->stepExecution->incrementSummaryInfo($action);
                }
            }
        }
    }
}
