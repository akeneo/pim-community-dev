<?php

namespace Akeneo\Tool\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Generic writer for basic objects, writes them in DB and detach them from the UOW to free the memory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Writer implements ItemWriterInterface, StepExecutionAwareInterface
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
     * @param array $objects
     */
    protected function incrementCount(array $objects)
    {
        foreach ($objects as $object) {
            if ($object->getId()) {
                $this->stepExecution->incrementSummaryInfo('process');
            } else {
                $this->stepExecution->incrementSummaryInfo('create');
            }
        }
    }
}
