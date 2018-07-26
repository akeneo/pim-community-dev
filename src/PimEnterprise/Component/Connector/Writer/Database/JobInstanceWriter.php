<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Dedicated writer for job instance only used for installation
 *
 * This writer give the option `is_installation` to the saver, this option is used by the default permission subscriber
 * to know if we have to apply default user group on job instance, this subscriber is needeed for ui concern and plug on
 * the generic `StorageEvents::POST_SAVE` event, to add default user group on job instance creation if not the job is never
 * editable on the ui.
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class JobInstanceWriter implements ItemWriterInterface, StepExecutionAwareInterface
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
    public function write(array $objects): void
    {
        $this->incrementCount($objects);
        $this->bulkSaver->saveAll($objects, ['is_installation' => true]);
        $this->bulkDetacher->detachAll($objects);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param array $objects
     */
    protected function incrementCount(array $objects): void
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
