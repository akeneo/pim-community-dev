<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Enrichment\Filter\ChannelAndLocalesFilter;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesEnrichedValuesTaskletSpec extends ObjectBehavior
{
    function let(
        CategoryDataCleaner $categoryDataCleaner,
    )
    {
        $this->beConstructedWith(
            $categoryDataCleaner,
        );
    }

    function it_is_initializable()
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(CleanCategoriesEnrichedValuesTasklet::class);
    }

    function it_calls_cleaning_service_with_deleted_channel_code(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        CategoryDataCleaner $categoryDataCleaner,
        ChannelAndLocalesFilter $channelAndLocalesFilter,
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $deletedChannel = 'deleted_channel_code';
        $jobParameters->get('channel_code')->willReturn($deletedChannel);
        $jobParameters->get('locales_codes')->willReturn(null);
        $jobParameters->get('action')->willReturn(ChannelAndLocalesFilter::CLEAN_CHANNEL_ACTION);

        $categoryDataCleaner->__invoke(
            [
                'channel_code' => $deletedChannel,
                'locales_codes' => null,
                'action' => 'cleanChannel',
            ],
            new ChannelAndLocalesFilter()
        )->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
