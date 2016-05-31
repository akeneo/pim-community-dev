<?php

namespace Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;

/**
 * A Step implementation that provides ability to transfer files.
 * @see TransferStepElementInterface
 *
 * The "originalFilename" will be set in the global job execution context.
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

        // we set the originalFilename in the execution context
        // to be able to know the extension of the file during the validation step
        $stepExecution->getJobExecution()->getExecutionContext()->put(
            'originalFilename',
            $this->transfer->getOriginalFilename()
        );

        $this->transfer->transfer();
    }
}
