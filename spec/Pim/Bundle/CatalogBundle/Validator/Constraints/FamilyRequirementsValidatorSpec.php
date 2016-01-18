<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\FamilyRequirements;
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

        $this->validate($family, $minimumRequirements);
    }
}

