<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Normalization;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\BulkMediaFetcher;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

class RecordProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private StepExecution $stepExecution;
    private BulkMediaFetcher $mediaFetcher;

    public function __construct(BulkMediaFetcher $mediaFetcher)
    {
        $this->mediaFetcher = $mediaFetcher;
    }

    public function process($item)
    {
        Assert::isInstanceOf($item, Record::class);

        if (true === $this->stepExecution->getJobParameters()->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()->get(
                JobInterface::WORKING_DIRECTORY_PARAMETER
            );
            $this->mediaFetcher->fetchAll($item->getValues(), $directory, $item->getCode()->__toString());
            foreach ($this->mediaFetcher->getErrors() as $error) {
                $this->stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']));
            }
        }

        return $item->normalize();
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
