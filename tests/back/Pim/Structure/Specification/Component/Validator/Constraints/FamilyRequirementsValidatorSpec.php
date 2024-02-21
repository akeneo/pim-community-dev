<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyRequirements;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyRequirementsValidatorSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        FamilyRequirements $minimumRequirements,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($attributeRepository, $channelRepository);
        $this->initialize($context);
    }

    public function it_is_a_constraint_validator()
    {
        $this->shouldImplement('Symfony\Component\Validator\ConstraintValidatorInterface');
    }

    public function it_validates_families_with_identifier_requirements(
        $attributeRepository,
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $requirementEcommerce,
        AttributeRequirementInterface $requirementMobile
    ) {
        $requirementEcommerce->isRequired()->willReturn(true);
        $requirementMobile->isRequired()->willReturn(true);
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

    public function it_validates_families_without_identifier_requirements(
        $attributeRepository,
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $requirementEcommerce,
        AttributeRequirementInterface $requirementMobile
    ) {
        $requirementEcommerce->isRequired()->willReturn(true);
        $requirementMobile->isRequired()->willReturn(true);
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
            ->shouldNotBeCalled();

        $this->validate($family, $minimumRequirements);
    }

    public function it_does_not_validate_family_with_required_attribute_not_present(
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $requirementEcommerce,
        ConstraintViolationBuilderInterface $violation
    ) {
        $requirementEcommerce->isRequired()->willReturn(true);
        $requirementEcommerce->getAttributeCode()->willReturn('color');
        $requirementEcommerce->getChannelCode()->willReturn('ecommerce');

        $family->getCode()->willReturn('familyCode');
        $family->getAttributeRequirements()->willReturn([$requirementEcommerce]);
        $family->getAttributeCodes()->willReturn(['sku']);

        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'mobile', 'print']);

        $context->buildViolation(Argument::any(), Argument::any())
            ->willReturn($violation)
            ->shouldBeCalled();

        $violation->atPath(Argument::any())->willReturn($violation);
        $violation->addViolation(Argument::any())->shouldBeCalled();

        $this->validate($family, $minimumRequirements);
    }

    public function it_does_validate_family_with_attribute_not_required(
        $channelRepository,
        $context,
        $minimumRequirements,
        FamilyInterface $family,
        AttributeRequirementInterface $requirementEcommerce
    ) {
        $requirementEcommerce->isRequired()->willReturn(false);
        $requirementEcommerce->getAttributeCode()->willReturn('color');
        $requirementEcommerce->getChannelCode()->willReturn('ecommerce');

        $family->getCode()->willReturn('familyCode');
        $family->getAttributeRequirements()->willReturn([$requirementEcommerce]);
        $family->getAttributeCodes()->willReturn(['sku']);

        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'mobile', 'print']);

        $context->buildViolation(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->validate($family, $minimumRequirements);
    }
}
