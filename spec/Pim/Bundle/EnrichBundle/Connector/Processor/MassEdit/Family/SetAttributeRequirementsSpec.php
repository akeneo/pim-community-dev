<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory
    ) {
        $this->beConstructedWith(
            $jobConfigurationRepo,
            $attributeRepository,
            $channelRepository,
            $factory
        );
    }

    function it_is_a_processor_and_a_step_element()
    {
        $this->beAnInstanceOf('\Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_processes_a_family(
        $jobConfigurationRepo,
        $attributeRepository,
        $channelRepository,
        $factory,
        StepExecution $stepExecution,
        ValidatorInterface $validator,
        FamilyInterface $family,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
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

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'actions' => $actions])
        );

        $factory->createAttributeRequirement($attributeColor, $channelMobile, true)->willReturn($attrReqColorMobile);
        $factory->createAttributeRequirement($attributeColor, $channelEcommerce, false)->willReturn($attrReqColorEcom);

        $this->setStepExecution($stepExecution);

        $family->addAttribute($attributeColor)->shouldBeCalledTimes(2);
        $family->addAttributeRequirement($attrReqColorMobile)->shouldBeCalled();
        $family->addAttributeRequirement($attrReqColorEcom)->shouldBeCalled();

        $this->process($family);
    }
}
