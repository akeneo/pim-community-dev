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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Tasklet\RemoveAttributeFromAttributeMappingTasklet;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

class RemoveAttributeFromAttributeMappingTaskletSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        GetAttributesMappingByFamilyHandler $getAttributesMappingHandler,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingHandler
    ): void {
        $this->beConstructedWith($getAttributesMappingHandler, $updateAttributesMappingHandler);

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet_for_removing_attribute_from_attribute_mapping(): void
    {
        $this->shouldBeAnInstanceOf(RemoveAttributeFromAttributeMappingTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }
}
