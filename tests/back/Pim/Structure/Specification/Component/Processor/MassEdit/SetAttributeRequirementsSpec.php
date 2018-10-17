<?php

namespace Specification\Akeneo\Pim\Structure\Component\Processor\MassEdit;

use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
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
        $this->beAnInstanceOf('\Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface');
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

    function it_adds_warnings_if_the_family_has_some_violations(
        $attributeRepository,
        $channelRepository,
        $factory,
        $validator,
        $detacher,
        StepExecution $stepExecution,
        FamilyInterface $family,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        AttributeInterface $colorAttribute,
        AttributeInterface $imageAttribute,
        ChannelInterface $channelMobile,
        AttributeRequirementInterface $attrReqColorMobile,
        AttributeRequirementInterface $attrReqImageMobile
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
                    'attribute_code' => 'image',
                    'channel_code'   => 'mobile',
                    'is_required'    => false
                ]
            ]
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);
        $attributeRepository->findOneByIdentifier('image')->willReturn($imageAttribute);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);

        $factory->createAttributeRequirement($colorAttribute, $channelMobile, true)->willReturn($attrReqColorMobile);
        $factory->createAttributeRequirement($imageAttribute, $channelMobile, false)->willReturn($attrReqImageMobile);

        $violation = new ConstraintViolation('Attribute does not belong to family', 'spec', [], '', '', $family);
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($family)->willReturn($violations);

        $stepExecution->addWarning(Argument::type('string'), [], Argument::type(DataInvalidItem::class))->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_families')->shouldBeCalled();
        $detacher->detach($family)->shouldBeCalled();

        $this->process($family)->shouldReturn(null);
    }
}
