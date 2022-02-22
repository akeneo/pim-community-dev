<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpsertProductHandlerSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
    ) {
        $this->beConstructedWith($validator, $productRepository, $productBuilder, $productSaver, $productUpdater, $productValidator);
    }

    function it_is_intializable()
    {
        $this->shouldHaveType(UpsertProductHandler::class);
    }

    function it_creates_updates_and_saves_a_product(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn(null);
        $productBuilder->createProduct('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();

        $this->__invoke($command);
    }

    function it_fetches_updates_and_saves_a_product(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productBuilder->createProduct('identifier1')->shouldNotBeCalled();
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();

        $this->__invoke($command);
    }

    function it_throws_an_exception_when_command_is_not_valid(
        ValidatorInterface $validator,
        SaverInterface $productSaver
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn($violations);
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(new ViolationsException($violations))->during('__invoke', [$command]);
    }

    function it_throws_an_exception_when_product_is_not_valid(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn($violations);
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(new LegacyViolationsException($violations))->during('__invoke', [$command]);
    }

    function it_throws_an_exception_when_updater_throws_an_exception(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1', valuesUserIntent: [new SetTextValue('name', null, null, 'foo')]);
        $product = new Product();

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productUpdater->update($product, Argument::cetera())->shouldbeCalledOnce()->willThrow(
            InvalidPropertyException::expected('error', 'class')
        );
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(LegacyViolationsException::class)->during('__invoke', [$command]);
    }

    function it_updates_a_product_with_a_set_text_value(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1', valuesUserIntent: [new SetTextValue('name', null, null, 'foo')]);
        $product = new Product();

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);

        $productUpdater->update($product, Argument::cetera())->shouldbeCalledOnce();
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();

        $this->__invoke($command);
    }

    function it_throws_an_error_when_user_intent_cannot_be_handled(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
    ) {
        $unknownUserIntent = new class implements ValueUserIntent {
            public function attributeCode(): string
            {
                return 'a_text';
            }
            public function value(): mixed
            {
                return 'new value';
            }
            public function localeCode(): ?string
            {
                return null;
            }
            public function channelCode(): ?string
            {
                return null;
            }
        };
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'identifier', valuesUserIntent: [
            $unknownUserIntent
        ]);

        $product = new Product();

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier')->shouldBeCalledOnce()->willReturn($product);

        $productUpdater->update($product, Argument::cetera())->shouldNotbeCalled();
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$command]);
    }
}
