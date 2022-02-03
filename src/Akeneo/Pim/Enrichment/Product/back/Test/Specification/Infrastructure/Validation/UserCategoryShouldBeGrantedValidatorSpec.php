<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Domain\Model\Permission\AccessLevel;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\IsUserCategoryGranted;
use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\Feature;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserCategoryShouldBeGranted;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserCategoryShouldBeGrantedValidator;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;

class UserCategoryShouldBeGrantedValidatorSpec extends ObjectBehavior
{
    function let(
        IsUserCategoryGranted $isUserCategoryGranted,
        ProductRepositoryInterface $productRepository,
        Feature $feature,
        ExecutionContext $context
    ) {
        $this->beConstructedWith($isUserCategoryGranted, $productRepository, $feature);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserCategoryShouldBeGrantedValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'foo');

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new UserCategoryShouldBeGranted([])]);
    }

    function it_does_nothing_when_permission_feature_is_not_activated(Feature $feature, ExecutionContext $context)
    {
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'foo');
        $feature->isEnabled(Feature::PERMISSION)->shouldBeCalledOnce()->willReturn(false);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new UserCategoryShouldBeGranted());
    }

    function it_does_nothing_when_product_does_not_exist(
        ProductRepositoryInterface $productRepository,
        Feature $feature,
        ExecutionContext $context
    ) {
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'foo');
        $feature->isEnabled(Feature::PERMISSION)->shouldBeCalledOnce()->willReturn(true);
        $productRepository->findOneByIdentifier('foo')->shouldBeCalledOnce()->willReturn(null);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new UserCategoryShouldBeGranted());
    }

    function it_validates_the_user_category_is_granted(
        IsUserCategoryGranted $isUserCategoryGranted,
        ProductRepositoryInterface $productRepository,
        Feature $feature,
        ExecutionContext $context
    ) {
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'foo');

        $feature->isEnabled(Feature::PERMISSION)->shouldBeCalledOnce()->willReturn(true);
        $productRepository->findOneByIdentifier('foo')->shouldBeCalledOnce()->willReturn(new Product());
        $isUserCategoryGranted->forProductAndAccessLevel(1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS)
            ->shouldBeCalledOnce()->willReturn(true);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new UserCategoryShouldBeGranted());
    }
}
