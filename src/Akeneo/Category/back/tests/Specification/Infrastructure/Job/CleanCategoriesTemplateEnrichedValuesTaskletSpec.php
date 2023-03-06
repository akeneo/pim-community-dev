<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Enrichment\CleanCategoryDataLinkedToChannel;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesTemplateEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesTemplateEnrichedValuesTaskletSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(CleanCategoriesTemplateEnrichedValuesTasklet::class);
    }

    function it_calls_cleaning_service_with_deleted_channel_code(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $jobParameters->get('template_uuid')->willReturn('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
