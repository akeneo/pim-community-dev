<?php

namespace Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;

/**
 * A Step implementation that provides ability to transfer files.
 * @see TransferStepElementInterface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TransferStep extends AbstractStep
{
    /** @var TransferStepElementInterface */
    protected $transfer;

    /**
     * @param TransferStepElementInterface $transfer
     */
    public function setTransfer(TransferStepElementInterface $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->transfer->setStepExecution($stepExecution);
        $this->transfer->transfer();
    }
}
