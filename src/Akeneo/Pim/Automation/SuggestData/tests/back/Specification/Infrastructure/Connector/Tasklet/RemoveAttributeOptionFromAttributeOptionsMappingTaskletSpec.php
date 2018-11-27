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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Tasklet\RemoveAttributeOptionFromAttributeOptionsMappingTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveAttributeOptionFromAttributeOptionsMappingTaskletSpec extends ObjectBehavior
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
        $this->shouldBeAnInstanceOf(RemoveAttributeOptionFromAttributeOptionsMappingTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_update_attribute_option_mapping(
        $familyCodesByAttributeQuery,
        $getAttributesMappingHandler,
        $getAttributeOptionsMappingHandler,
        $saveAttributeOptionsMappingHandler
    ): void {
        $familyCodesByAttributeQuery->execute('pim_color')->willReturn(['router']);

        $attributesMapping = new AttributesMappingResponse();
        $attributesMapping->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', 1, null));

        $getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery('router'))->willReturn($attributesMapping);

        $attributeOptionsMapping = new AttributeOptionsMapping('router', 'franklin_color', [
            new AttributeOptionMapping('franklin_red', 'red', 0, 'pim_red'),
            new AttributeOptionMapping('franklin_blue', 'blue', 0, 'pim_blue'),
        ]);

        $getAttributeOptionsMappingHandler->handle(
            new GetAttributeOptionsMappingQuery(new FamilyCode('router'), new FranklinAttributeId('franklin_color'))
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
}
