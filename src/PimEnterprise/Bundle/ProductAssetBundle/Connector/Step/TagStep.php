<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Connector\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;

/**
 * Tag Step for assets
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class TagStep extends ItemStep
{

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $processedItems = [];
        $writeCount     = 0;

        $this->initializeStepElements($stepExecution);

        $stopExecution = false;
        while (!$stopExecution) {
            try {
                $readItem = $this->reader->read();
                if (null === $readItem) {
                    $stopExecution = true;
                    continue;
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($this->stepExecution, $this->reader, $e);

                continue;
            }

            $processedItems = $this->process($readItem);
            if (null !== $processedItems) {
                $writeCount++;
                $this->write($processedItems);
                $processedItems = [];
                $this->getJobRepository()->updateStepExecution($stepExecution);
            }
        }

        if (count($processedItems) > 0) {
            $this->write($processedItems);
        }
        $this->flushStepElements();
    }
}
