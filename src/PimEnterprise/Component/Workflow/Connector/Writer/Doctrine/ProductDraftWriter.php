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

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Connector\Writer\Doctrine\BaseWriter;

/**
 * Product draft writer
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftWriter extends BaseWriter
{
    /** @var RemoverInterface */
    private $remover;

    /**
     * @param SaverInterface              $bulkSaver
     * @param BulkObjectDetacherInterface $bulkDetacher
     * @param RemoverInterface            $remover
     */
    public function __construct(
        SaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        RemoverInterface $remover
    ) {
        parent::__construct($bulkSaver, $bulkDetacher);

        $this->bulkSaver    = $bulkSaver;
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
                $this->bulkSaver->save($productDraft);
            }
        }

        $this->bulkDetacher->detachAll($productDrafts);
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
