<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyRequirements;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyRequirementsValidatorSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        FamilyRequirements $minimumRequirements,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($attributeRepository, $channelRepository);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement('Symfony\Component\Validator\ConstraintValidatorInterface');
    }

    function it_validates_families_with_identifier_requirements(
        $attributeRepository,
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $requirementEcommerce,
        AttributeRequirementInterface $requirementMobile
    ) {
        $family->getAttributeRequirements()->willReturn([$requirementEcommerce, $requirementMobile]);
        $family->getAttributeCodes()->willReturn(['sku','ecommerce']);
        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $requirementEcommerce->getAttributeCode()->willReturn('sku');
        $requirementEcommerce->getChannelCode()->willReturn('ecommerce');
        $requirementMobile->getAttributeCode()->willReturn('sku');
        $requirementMobile->getChannelCode()->willReturn('mobile');

        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'mobile']);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($family, $minimumRequirements);
    }

    function it_does_not_validate_families_with_missing_identifier_requirements(
        $attributeRepository,
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $requirementEcommerce,
        AttributeRequirementInterface $requirementMobile,
        ConstraintViolationBuilderInterface $violation
    ) {
        $family->getAttributeRequirements()->willReturn([$requirementEcommerce, $requirementMobile]);
        $family->getAttributeCodes()->willReturn(['sku','ecommerce']);
        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $requirementEcommerce->getAttributeCode()->willReturn('sku');
        $requirementEcommerce->getChannelCode()->willReturn('ecommerce');
        $requirementMobile->getAttributeCode()->willReturn('sku');
        $requirementMobile->getChannelCode()->willReturn('mobile');

        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'mobile', 'print']);

        $family->getCode()->willReturn('familyCode');
        $context->buildViolation(Argument::any(), Argument::any())
            ->willReturn($violation)
            ->shouldBeCalled();
        $violation->atPath(Argument::any())->willReturn($violation);
        $violation->addViolation(Argument::any())->shouldBeCalled();

        $this->validate($family, $minimumRequirements);
    }

    function it_does_not_validate_family_with_required_attribute_not_present(
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement,
        ConstraintViolationBuilderInterface $violation
    ) {
        $family->getCode()->willReturn('familyCode');
        $family->getAttributeRequirements()->willReturn([$attributeRequirement]);
        $family->getAttributeCodes()->willReturn([]);
        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $context->buildViolation(Argument::any(), Argument::any())
            ->willReturn($violation)
            ->shouldBeCalled();

        $violation->atPath(Argument::any())->willReturn($violation);
        $violation->addViolation(Argument::any())->shouldBeCalled();

        $this->validate($family, $minimumRequirements);
    }
}
