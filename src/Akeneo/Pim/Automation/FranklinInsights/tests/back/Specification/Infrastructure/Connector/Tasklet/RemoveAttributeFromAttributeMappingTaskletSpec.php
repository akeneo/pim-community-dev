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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet\RemoveAttributeFromAttributeMappingTasklet;
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
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingHandler
    ): void {
        $this->beConstructedWith($getAttributesMappingHandler, $saveAttributesMappingHandler);

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
        $saveAttributesMappingHandler
    ): void {
        $franklinResponse = new AttributesMappingResponse();
        $franklinResponse
            ->addAttribute(new AttributeMapping('franklin_size', null, 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null))
            ->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null))
            ->addAttribute(new AttributeMapping('franklin_weight', null, 'text', 'pim_weight', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null));

        $getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($franklinResponse);

        $saveAttributesMappingHandler->handle(new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), [
            'franklin_size' => [
                'franklinAttribute' => ['type' => 'text'],
                'attribute' => null,
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
        $saveAttributesMappingHandler
    ): void {
        $franklinResponse = new AttributesMappingResponse();
        $franklinResponse->addAttribute(new AttributeMapping('franklin_weight', null, 'text', 'pim_weight', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null));

        $getAttributesMappingHandler
            ->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($franklinResponse);

        $saveAttributesMappingHandler->handle()->shouldNotBeCalled();

        $this->execute();
    }

    public function it_does_not_update_the_mapping_if_the_mapping_is_empty(
        $getAttributesMappingHandler,
        $saveAttributesMappingHandler
    ): void {
        $getAttributesMappingHandler
            ->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn(new AttributesMappingResponse());

        $saveAttributesMappingHandler->handle()->shouldNotBeCalled();

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
