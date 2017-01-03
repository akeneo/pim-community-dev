<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $channelRepository,
            $factory,
            $validator,
            $detacher
        );
    }

    function it_is_a_processor()
    {
        $this->beAnInstanceOf('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_processes_a_family(
        $attributeRepository,
        $channelRepository,
        $factory,
        StepExecution $stepExecution,
        ValidatorInterface $validator,
        FamilyInterface $family,
        JobExecution $jobExecution,
        AttributeInterface $attributeColor,
        ChannelInterface $channelMobile,
        ChannelInterface $channelEcommerce,
        AttributeRequirementInterface $attrReqColorMobile,
        AttributeRequirementInterface $attrReqColorEcom,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn(
            [
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
            ]
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($family)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $attributeRepository->findOneByIdentifier('color')->willReturn($attributeColor);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channelEcommerce);

        $factory->createAttributeRequirement($attributeColor, $channelMobile, true)->willReturn($attrReqColorMobile);
        $factory->createAttributeRequirement($attributeColor, $channelEcommerce, false)->willReturn($attrReqColorEcom);

        $this->setStepExecution($stepExecution);

        $family->addAttribute($attributeColor)->shouldBeCalledTimes(2);
        $family->addAttributeRequirement($attrReqColorMobile)->shouldBeCalled();
        $family->addAttributeRequirement($attrReqColorEcom)->shouldBeCalled();

        $this->process($family);
    }
}
