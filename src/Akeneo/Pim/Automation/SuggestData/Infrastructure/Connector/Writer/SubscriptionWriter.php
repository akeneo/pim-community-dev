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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        // TODO APAI-154: Implement actual subscription
        foreach ($items as $item) {
            $this->stepExecution->incrementSummaryInfo('subscribed');
        }
    }
}
