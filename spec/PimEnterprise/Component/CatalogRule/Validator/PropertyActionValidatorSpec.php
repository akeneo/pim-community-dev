<?php

namespace spec\PimEnterprise\Component\CatalogRule\Validator;

use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\PropertyAction;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyAction;
use PimEnterprise\Component\CatalogRule\Validator\PropertyActionValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class PropertyActionValidatorSpec extends ObjectBehavior
{
    function let(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($applierRegistry, $productBuilder, $validator, $attributeRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertyActionValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_throws_exception_if_it_is_not_an_action(Constraint $constraint) {

        $this->shouldThrow(
            new \LogicException('Action of type "object" can not be validated.')
        )->during('validate', [new \stdClass(), $constraint]);
    }

    function it_skips_validation_if_the_faked_identifier_is_invalid(
        $applierRegistry,
        $productBuilder,
        $validator,
        $attributeRepository,
        $context,
        ProductCopyAction $productAction,
        PropertyAction $constraint,
        ProductInterface $fakeProduct,
        ActionApplierInterface $actionApplierInterface,
        AttributeInterface $identifierAttribute
    ) {
        $productBuilder->createProduct(Argument::cetera())->willReturn($fakeProduct);

        $applierRegistry->getActionApplier($productAction)->willReturn($actionApplierInterface);
        $actionApplierInterface->applyAction($productAction, [$fakeProduct])->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'values[sku].varchar', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($fakeProduct)->willReturn($violations);

        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');
        $identifierAttribute->getBackendType()->willReturn('varchar');

        $context->buildViolation(
            Argument::any(),
            Argument::any()
        )->shouldNotBeCalled();

        $this->validate($productAction, $constraint);
    }

    function it_adds_a_violation_if_product_is_not_valid(
        $applierRegistry,
        $productBuilder,
        $validator,
        $attributeRepository,
        $context,
        ProductCopyAction $productAction,
        PropertyAction $constraint,
        ProductInterface $fakeProduct,
        ActionApplierInterface $actionApplierInterface,
        ConstraintViolationBuilderInterface $violationBuilder,
        AttributeInterface $identifierAttribute
    ) {
        $constraint->message = 'foo';
        $productBuilder->createProduct(Argument::cetera())->willReturn($fakeProduct);

        $applierRegistry->getActionApplier($productAction)->willReturn($actionApplierInterface);
        $actionApplierInterface->applyAction($productAction, [$fakeProduct])->shouldBeCalled();

        $violationOne = new ConstraintViolation('ErrorOne', 'fooOne', [], 'barOne', 'values[sku].varchar', 'mycodeOne');
        $violationTwo = new ConstraintViolation('ErrorTwo', 'fooTwo', [], 'barTwo', 'values[foo].varchar', 'mycodeTwo');
        $violations = new ConstraintViolationList([$violationOne, $violationTwo]);
        $validator->validate($fakeProduct)->willReturn($violations);

        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');
        $identifierAttribute->getBackendType()->willReturn('varchar');

        $context->buildViolation(
            'foo',
            [
                '%message%' => 'ErrorTwo',
            ]
        )->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($productAction, $constraint);
    }
}
