<?php

namespace spec\Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Model\JobConfiguration;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        MassEditRepositoryInterface $massEditRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory
    ) {
        $this->beConstructedWith(
            $massEditRepository,
            $attributeRepository,
            $channelRepository,
            $factory
        );
    }

    function it_is_a_processor_and_a_step_element()
    {
        $this->beAnInstanceOf('\Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('\Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
    }

    function it_processes_a_family(
        $massEditRepository,
        $attributeRepository,
        $channelRepository,
        $factory,
        StepExecution $stepExecution,
        ValidatorInterface $validator,
        FamilyInterface $family,
        JobExecution $jobExecution,
        JobConfiguration $jobConfiguration,
        AttributeInterface $attributeColor,
        ChannelInterface $channelMobile,
        ChannelInterface $channelEcommerce,
        AttributeRequirementInterface $attrReqColorMobile,
        AttributeRequirementInterface $attrReqColorEcom
    ) {
        $actions = [
            [
                'attribute_code' => 'color',
                'channel_code'   => 'mobile',
                'is_required'    => true
            ],
            [
                'attribute_code' => 'color',
                'channel_code'   => 'ecommerce',
                'is_required'    => false
            ]
        ];

        $violations = new ConstraintViolationList([]);
        $validator->validate($family)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $attributeRepository->findOneByIdentifier('color')->willReturn($attributeColor);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channelEcommerce);

        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'actions' => $actions])
        );

        $factory->createAttributeRequirement($attributeColor, $channelMobile, true)->willReturn($attrReqColorMobile);
        $factory->createAttributeRequirement($attributeColor, $channelEcommerce, false)->willReturn($attrReqColorEcom);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $family->addAttribute($attributeColor)->shouldBeCalledTimes(2);
        $family->addAttributeRequirement($attrReqColorMobile)->shouldBeCalled();
        $family->addAttributeRequirement($attrReqColorEcom)->shouldBeCalled();

        $this->process($family);
    }
}
