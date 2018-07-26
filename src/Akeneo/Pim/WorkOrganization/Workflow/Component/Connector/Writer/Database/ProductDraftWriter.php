<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * Product draft writer
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var SaverInterface */
    protected $saver;

    /** @var BulkObjectDetacherInterface */
    protected $bulkDetacher;

    /** @var RemoverInterface */
    private $remover;

    /**
     * @param SaverInterface              $saver
     * @param BulkObjectDetacherInterface $bulkDetacher
     * @param RemoverInterface            $remover
     */
    public function __construct(
        SaverInterface $saver,
        BulkObjectDetacherInterface $bulkDetacher,
        RemoverInterface $remover
    ) {
        $this->saver = $saver;
        $this->bulkDetacher = $bulkDetacher;
        $this->remover = $remover;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $productDrafts)
    {
        $this->incrementCount($productDrafts);
        foreach ($productDrafts as $productDraft) {
            $changes = $productDraft->getChanges();
            if ($productDraft->getId() && empty($changes)) {
                $this->remover->remove($productDraft);
            } else {
                $this->saver->save($productDraft);
            }
        }

        $this->bulkDetacher->detachAll($productDrafts);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param array $productDrafts
     */
    protected function incrementCount(array $productDrafts)
    {
        foreach ($productDrafts as $productDraft) {
            if ($productDraft->getId()) {
                $changes = $productDraft->getChanges();
                $info = empty($changes) ? 'proposal_deleted' : 'proposal_updated';
                $this->stepExecution->incrementSummaryInfo($info);
            } else {
                $this->stepExecution->incrementSummaryInfo('proposal_created');
            }
        }
    }
}
