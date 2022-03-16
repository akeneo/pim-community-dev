<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->beConstructedWith(
            $validator,
            $productRepository,
            $productBuilder,
            $productSaver,
            $productUpdater,
            $productValidator,
            $eventDispatcher
        );
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
        EventDispatcherInterface $eventDispatcher,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn(null);
        $productBuilder->createProduct('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();
        $event = new ProductWasCreated('identifier1');
        $eventDispatcher->dispatch($event)->shouldBeCalledOnce()->willReturn($event);

        $this->__invoke($command);
    }

    function it_fetches_updates_and_saves_a_product(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productBuilder->createProduct('identifier1')->shouldNotBeCalled();
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();
        $event = new ProductWasUpdated('identifier1');
        $eventDispatcher->dispatch($event)->shouldBeCalledOnce()->willReturn($event);

        $this->__invoke($command);
    }

    function it_does_not_dispatch_event_when_product_was_not_updated(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
        ProductInterface $product
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product->getIdentifier()->willReturn('identifier1');
        $product->isDirty()->willReturn(false);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productBuilder->createProduct('identifier1')->shouldNotBeCalled();
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();

        $eventDispatcher->dispatch(Argument::type(ProductWasCreated::class))->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(ProductWasUpdated::class))->shouldNotBeCalled();

        $this->__invoke($command);
    }

    function it_throws_an_exception_when_command_is_not_valid(
        ValidatorInterface $validator,
        SaverInterface $productSaver
    ) {
        $command = new UpsertProductCommand(1, 'identifier1');
        $product = new Product();
        $product->setIdentifier('identifier1');
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
        $product->setIdentifier('identifier1');
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
        $command = new UpsertProductCommand(1, 'identifier1', valueUserIntents: [new SetTextValue('name', null, null, 'foo')]);
        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productUpdater->update($product, Argument::cetera())->shouldbeCalledOnce()->willThrow(
            InvalidPropertyException::expected('error', 'class')
        );
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$command]);
    }

    function it_updates_a_product_with_a_set_text_value(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $command = new UpsertProductCommand(1, 'identifier1', valueUserIntents: [new SetTextValue('name', null, null, 'foo')]);
        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);

        $productUpdater->update($product, Argument::cetera())->shouldbeCalledOnce();
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();
        $event = new ProductWasUpdated('identifier1');
        $eventDispatcher->dispatch($event)->shouldBeCalledOnce()->willReturn($event);

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
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'identifier', valueUserIntents: [
            $unknownUserIntent
        ]);

        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productRepository->findOneByIdentifier('identifier')->shouldBeCalledOnce()->willReturn($product);

        $productUpdater->update($product, Argument::cetera())->shouldNotbeCalled();
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$command]);
    }
}
