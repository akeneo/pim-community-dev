<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\Constraints\ValidIdentifier;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidIdentifierValidatorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $identifiableObjectRepository)
    {
        $this->beConstructedWith($identifiableObjectRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\ValidIdentifierValidator');
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
    }
    
    function it_validates_the_identifier(
        $identifiableObjectRepository,
        ValidIdentifier $validIdentifier,
        ProductInterface $product,
        ExecutionContextInterface $executionContext
    ) {
        $this->initialize($executionContext);
        
        $identifiableObjectRepository->findOneByIdentifier('sku')->willReturn($product);
        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('sku', $validIdentifier)->shouldReturn(null);
    }
    
    function it_does_not_valid_empty_value(
        $identifiableObjectRepository,
        ValidIdentifier $validIdentifier,
        ExecutionContextInterface $executionContext
    ) {
        $this->initialize($executionContext);

        $identifiableObjectRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('', $validIdentifier)->shouldReturn(null);
    }

    function it_add_violation_when_the_identifier_is_valid(
        $identifiableObjectRepository,
        ExecutionContextInterface $executionContext,
        ProductInterface $product,
        ValidIdentifier $validIdentifier,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $this->initialize($executionContext);
        
        $identifiableObjectRepository->findOneByIdentifier('sku')->willReturn($product);
        $identifiableObjectRepository->findOneByIdentifier('wrong_sku')->willReturn(null);
        
        $executionContext->buildViolation(Argument::type('string'))->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate('sku,wrong_sku', $validIdentifier)->shouldReturn(null);
    }

    function it_throws_an_exception_when_the_constraint_is_invalid(Range $range)
    {
        $this->shouldThrow('Symfony\Component\Validator\Exception\UnexpectedTypeException')
            ->during('validate', ['sku', $range]);
    }
}
