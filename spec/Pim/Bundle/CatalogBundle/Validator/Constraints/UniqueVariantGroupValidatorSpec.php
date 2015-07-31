<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantGroup;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueVariantGroupValidatorSpec extends ObjectBehavior
{
    function let(UniqueVariantGroup $onlyOneVariantGroup, ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement('Symfony\Component\Validator\ConstraintValidatorInterface');
    }

    function it_validates_products_with_one_variant_group(
        $context,
        $onlyOneVariantGroup,
        ProductInterface $mug,
        GroupInterface $mugVariantGroup,
        GroupInterface $otherGroup,
        GroupTypeInterface $variantType,
        GroupTypeInterface $groupType
    ) {
        $mug->getGroups()->willReturn([$mugVariantGroup, $otherGroup]);

        $mugVariantGroup->getType()->willReturn($variantType);
        $otherGroup->getType()->willReturn($groupType);

        $variantType->isVariant()->willReturn(true);
        $groupType->isVariant()->willReturn(false);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($mug, $onlyOneVariantGroup);
    }

    function it_does_not_validate_products_with_multiple_variant_group(
        $context,
        $onlyOneVariantGroup,
        ProductInterface $mug,
        CustomGroupInterface $mugVariantGroup,
        CustomGroupInterface $otherGroup,
        GroupTypeInterface $variantType,
        ConstraintViolationBuilderInterface $violation
    ) {
        $mug->getGroups()->willReturn([$mugVariantGroup, $otherGroup]);

        $mugVariantGroup->getType()->willReturn($variantType);
        $mugVariantGroup->__toString()->willReturn('mug');
        $otherGroup->getType()->willReturn($variantType);
        $otherGroup->__toString()->willReturn('other');

        $variantType->isVariant()->willReturn(true);

        $mug->getIdentifier()->willReturn('mug');
        $context
            ->buildViolation($onlyOneVariantGroup->message, ['%groups%' => 'mug, other', '%product%' => 'mug'])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($mug, $onlyOneVariantGroup);
    }
}

interface CustomGroupInterface extends GroupInterface
{
    public function __toString();
}
