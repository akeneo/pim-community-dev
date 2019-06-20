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

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet\RemoveOptionFromAttributeOptionsMappingTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveOptionFromAttributeOptionsMappingTaskletSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        GetAttributesMappingByFamilyHandler $getAttributesMappingHandler,
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler,
        SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
    ): void {
        $this->beConstructedWith(
            $familyCodesByAttributeQuery,
            $getAttributesMappingHandler,
            $getAttributeOptionsMappingHandler,
            $saveAttributeOptionsMappingHandler
        );

        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'pim_attribute_code' => 'pim_color',
            'pim_attribute_option_code' => 'pim_red',
        ]));

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet_for_removing_attribute_option_from_attribute_options_mapping(): void
    {
        $this->shouldBeAnInstanceOf(RemoveOptionFromAttributeOptionsMappingTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_update_attribute_option_mapping(
        $familyCodesByAttributeQuery,
        $getAttributesMappingHandler,
        $getAttributeOptionsMappingHandler,
        $saveAttributeOptionsMappingHandler
    ): void {
        $familyCodesByAttributeQuery->execute('pim_color')->willReturn(['router']);
        $familyCode = new FamilyCode('router');

        $attributesMapping = new AttributesMappingResponse();
        $attributesMapping->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null));

        $getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($attributesMapping);

        $attributeOptionsMapping = new AttributeOptionsMapping($familyCode, 'franklin_color', [
            new AttributeOptionMapping('franklin_red', 'red', 0, new AttributeOptionCode('pim_red')),
            new AttributeOptionMapping('franklin_blue', 'blue', 0, new AttributeOptionCode('pim_blue')),
        ]);

        $getAttributeOptionsMappingHandler->handle(
            new GetAttributeOptionsMappingQuery($familyCode, new FranklinAttributeId('franklin_color'))
        )->willReturn($attributeOptionsMapping);

        $saveAttributeOptionsMappingHandler->handle(new SaveAttributeOptionsMappingCommand(
            new FamilyCode('router'),
            new AttributeCode('pim_color'),
            new FranklinAttributeId('franklin_color'),
            new AttributeOptions([
                'franklin_red' => [
                    'franklinAttributeOptionCode' => [
                        'label' => 'red',
                    ],
                    'catalogAttributeOptionCode' => null,
                    'status' => 0,
                ],
                'franklin_blue' => [
                    'franklinAttributeOptionCode' => [
                        'label' => 'blue',
                    ],
                    'catalogAttributeOptionCode' => 'pim_blue',
                    'status' => 0,
                ],
            ])
        ))->shouldBeCalled();

        $this->execute();
    }

    public function it_updates_nothing_when_no_families_uses_the_attribute(
        $familyCodesByAttributeQuery,
        $getAttributesMappingHandler,
        $getAttributeOptionsMappingHandler,
        $saveAttributeOptionsMappingHandler
    ): void {
        $familyCodesByAttributeQuery->execute('pim_color')->willReturn([]);

        $getAttributesMappingHandler->handle(Argument::any())->shouldNotBeCalled();
        $getAttributeOptionsMappingHandler->handle(Argument::any())->shouldNotBeCalled();
        $saveAttributeOptionsMappingHandler->handle(Argument::any())->shouldNotBeCalled();
    }

    public function it_updates_nothing_when_there_is_no_attribute_mapping(
        $familyCodesByAttributeQuery,
        $getAttributesMappingHandler,
        $getAttributeOptionsMappingHandler,
        $saveAttributeOptionsMappingHandler
    ): void {
        $familyCodesByAttributeQuery->execute('pim_color')->willReturn(['router']);

        $getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn(new AttributesMappingResponse());

        $getAttributeOptionsMappingHandler->handle(Argument::any())->shouldNotBeCalled();
        $saveAttributeOptionsMappingHandler->handle(Argument::any())->shouldNotBeCalled();
    }

    public function it_updates_nothing_when_there_is_no_attribute_option_mapping(
        $familyCodesByAttributeQuery,
        $getAttributesMappingHandler,
        $getAttributeOptionsMappingHandler,
        $saveAttributeOptionsMappingHandler
    ): void {
        $familyCodesByAttributeQuery->execute('pim_color')->willReturn(['router']);
        $familyCode = new FamilyCode('router');

        $attributesMapping = new AttributesMappingResponse();
        $attributesMapping->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null));

        $getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($attributesMapping);

        $getAttributeOptionsMappingHandler->handle(
            new GetAttributeOptionsMappingQuery($familyCode, new FranklinAttributeId('franklin_color'))
        )->willReturn(new AttributeOptionsMapping($familyCode, 'franklin_color', []));

        $saveAttributeOptionsMappingHandler->handle(Argument::any())->shouldNotBeCalled();

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
