<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\SetAction as DTOSetAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\PropertyAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\PropertyActionValidator;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($applierRegistry, $productBuilder, $validator, $attributeRepository, $chainedDenormalizer);
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

    function it_throws_exception_if_it_is_not_an_action(PropertyAction $constraint) {
        $this->shouldThrow(
            new \LogicException('Action of "stdClass" type can not be validated.')
        )->during('validate', [new \stdClass(), $constraint]);
    }

    function it_skips_validation_if_the_faked_identifier_is_invalid_given_a_dto(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context,
        PropertyAction $constraint,
        ProductInterface $fakeProduct,
        ActionApplierInterface $actionApplierInterface,
        ProductSetActionInterface $productAction
    ) {
        $action = new DTOSetAction(['field' => 'foo', 'value' => 'bar']);
        $chainedDenormalizer->denormalize(
            ['type' => 'set', 'field' => 'foo', 'value' => 'bar'],
            ActionInterface::class
        )->willReturn($productAction);

        $productBuilder->createProduct(Argument::cetera())->willReturn($fakeProduct);

        $applierRegistry->getActionApplier($productAction)->willReturn($actionApplierInterface);
        $actionApplierInterface->applyAction($productAction, [$fakeProduct])->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'values[sku-<all_channels>-<all_locales>]', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($fakeProduct)->willReturn($violations);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $context->buildViolation(
            Argument::any(),
            Argument::any()
        )->shouldNotBeCalled();

        $this->validate($action, $constraint);
    }

    function it_skips_validation_if_the_faked_identifier_is_invalid_given_a_model_action(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context,
        PropertyAction $constraint,
        ProductInterface $fakeProduct,
        ActionApplierInterface $actionApplierInterface,
        ProductSetActionInterface $productAction
    ) {
        $productAction = new ProductSetAction(['field' => 'foo', 'value' => 'bar']);
        $productBuilder->createProduct(Argument::cetera())->willReturn($fakeProduct);

        $applierRegistry->getActionApplier($productAction)->willReturn($actionApplierInterface);
        $actionApplierInterface->applyAction($productAction, [$fakeProduct])->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'values[sku-<all_channels>-<all_locales>]', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($fakeProduct)->willReturn($violations);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $context->buildViolation(
            Argument::any(),
            Argument::any()
        )->shouldNotBeCalled();

        $this->validate($productAction, $constraint);
    }

    function it_adds_a_violation_if_product_is_not_valid_given_a_dto(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context,
        PropertyAction $constraint,
        ProductInterface $fakeProduct,
        ActionApplierInterface $actionApplierInterface,
        ConstraintViolationBuilderInterface $violationBuilder,
        ProductSetActionInterface $productSetAction
    ) {
        $constraint->message = 'foo';
        $productBuilder->createProduct(Argument::cetera())->willReturn($fakeProduct);

        $action = new DTOSetAction(['field' => 'foo', 'value' => 'bar']);
        $chainedDenormalizer->denormalize(
            ['type' => 'set', 'field' => 'foo', 'value' => 'bar'],
            ActionInterface::class
        )->willReturn($productSetAction);

        $applierRegistry->getActionApplier($productSetAction)->willReturn($actionApplierInterface);
        $actionApplierInterface->applyAction($productSetAction, [$fakeProduct])->shouldBeCalled();

        $violationOne = new ConstraintViolation('ErrorOne', 'fooOne', [], 'barOne', 'values[sku-<all_channels>-<all_locales>]', 'mycodeOne');
        $violationTwo = new ConstraintViolation('ErrorTwo', 'fooTwo', [], 'barTwo', 'values[foo-<all_channels>-<all_locales>]', 'mycodeTwo');
        $violations = new ConstraintViolationList([$violationOne, $violationTwo]);
        $validator->validate($fakeProduct)->willReturn($violations);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $context->buildViolation(
            'foo',
            [
                '%message%' => 'ErrorTwo',
            ]
        )->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($action, $constraint);
    }

    function it_adds_a_violation_if_product_is_not_valid_given_a_model_action(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context,
        PropertyAction $constraint,
        ProductInterface $fakeProduct,
        ActionApplierInterface $actionApplierInterface,
        ConstraintViolationBuilderInterface $violationBuilder,
        ProductSetActionInterface $productSetAction
    ) {
        $constraint->message = 'foo';
        $productBuilder->createProduct(Argument::cetera())->willReturn($fakeProduct);

        $productSetAction = new ProductSetAction(['type' => 'set', 'field' => 'foo', 'value' => 'bar']);

        $applierRegistry->getActionApplier($productSetAction)->willReturn($actionApplierInterface);
        $actionApplierInterface->applyAction($productSetAction, [$fakeProduct])->shouldBeCalled();

        $violationOne = new ConstraintViolation('ErrorOne', 'fooOne', [], 'barOne', 'values[sku-<all_channels>-<all_locales>]', 'mycodeOne');
        $violationTwo = new ConstraintViolation('ErrorTwo', 'fooTwo', [], 'barTwo', 'values[foo-<all_channels>-<all_locales>]', 'mycodeTwo');
        $violations = new ConstraintViolationList([$violationOne, $violationTwo]);
        $validator->validate($fakeProduct)->willReturn($violations);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $context->buildViolation('foo', ['%message%' => 'ErrorTwo'])->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($productSetAction, $constraint);
    }
}
