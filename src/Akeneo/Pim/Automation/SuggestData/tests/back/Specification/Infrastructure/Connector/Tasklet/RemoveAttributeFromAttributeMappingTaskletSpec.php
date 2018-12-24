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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write\AttributeMapping as WriteAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Tasklet\RemoveAttributeFromAttributeMappingTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveAttributeFromAttributeMappingTaskletSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        GetAttributesMappingByFamilyHandler $getAttributesMappingHandler,
        SaveAttributesMappingByFamilyHandler $updateAttributesMappingHandler
    ): void {
        $this->beConstructedWith($getAttributesMappingHandler, $updateAttributesMappingHandler);

        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'pim_attribute_codes' => ['pim_color', 'pim_size'],
            'family_code' => 'router',
        ]));

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet_for_removing_attribute_from_attribute_mapping(): void
    {
        $this->shouldBeAnInstanceOf(RemoveAttributeFromAttributeMappingTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_calls_franklin_with_the_new_mapping(
        $getAttributesMappingHandler,
        $updateAttributesMappingHandler
    ): void {
        $franklinResponse = new AttributesMappingResponse();
        $franklinResponse
            ->addAttribute(new AttributeMapping('franklin_size', null, 'text', 'pim_size', 1, null))
            ->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', 1, null))
            ->addAttribute(new AttributeMapping('franklin_weight', null, 'text', 'pim_weight', 1, null));

        $getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery('router'))->willReturn($franklinResponse);

        $updateAttributesMappingHandler->handle(new SaveAttributesMappingByFamilyCommand('router', [
            'franklin_size' => [
                'franklinAttribute' => ['type' => 'text'],
                'attribute' => null,
                'status' => WriteAttributeMapping::ATTRIBUTE_PENDING,
            ],
            'franklin_color' => [
                'franklinAttribute' => ['type' => 'text'],
                'attribute' => null,
            ],
            'franklin_weight' => [
                'franklinAttribute' => ['type' => 'text'],
                'attribute' => 'pim_weight',
            ],
        ]))->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_call_franklin_if_the_attribute_is_not_in_family_attribute_mapping(
        $getAttributesMappingHandler,
        $updateAttributesMappingHandler
    ): void {
        $franklinResponse = new AttributesMappingResponse();
        $franklinResponse->addAttribute(new AttributeMapping('franklin_weight', null, 'text', 'pim_weight', 1, null));

        $getAttributesMappingHandler
            ->handle(new GetAttributesMappingByFamilyQuery('router'))
            ->willReturn($franklinResponse);

        $updateAttributesMappingHandler->handle()->shouldNotBeCalled();

        $this->execute();
    }

    public function it_does_not_update_the_mapping_if_the_mapping_is_empty(
        $getAttributesMappingHandler,
        $updateAttributesMappingHandler
    ): void {
        $getAttributesMappingHandler
            ->handle(new GetAttributesMappingByFamilyQuery('router'))
            ->willReturn(new AttributesMappingResponse());

        $updateAttributesMappingHandler->handle()->shouldNotBeCalled();

        $this->execute();
    }

    public function it_throws_an_exception_if_there_is_no_job_parameters($stepExecution): void
    {
        $stepExecution->getJobParameters()->willReturn(null);
        $this->shouldThrow(\InvalidArgumentException::class)->during('execute');
    }

    public function it_throws_an_exception_if_one_parameter_is_missing($stepExecution): void
    {
        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'wrong_key' => 'wrong_value',
        ]));
        $this->shouldThrow(\InvalidArgumentException::class)->during('execute');
    }
}
