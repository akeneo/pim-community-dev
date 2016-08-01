<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Connector\Writer\Doctrine;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;

/**
 * Product draft writer
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
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
        $this->saver        = $saver;
        $this->bulkDetacher = $bulkDetacher;
        $this->remover      = $remover;
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
