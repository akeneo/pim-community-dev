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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
        UserIntentApplierRegistry $applierRegistry,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $validator,
            $productRepository,
            $productBuilder,
            $productSaver,
            $productValidator,
            $eventDispatcher,
            $applierRegistry,
            $tokenStorage
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
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn(null);
        $productBuilder->createProduct('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();
        $event = new ProductWasCreated($product->getUuid());
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
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->setIdentifier('identifier1');
        $product->setCreated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->setUpdated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productBuilder->createProduct('identifier1')->shouldNotBeCalled();
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();
        $event = new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
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
        ProductInterface $product,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product->getIdentifier()->willReturn('identifier1');
        $product->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->isDirty()->willReturn(false);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
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
        SaverInterface $productSaver,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->setIdentifier('identifier1');
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn($violations);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(new ViolationsException($violations))->during('__invoke', [$command]);
    }

    function it_throws_an_exception_when_product_is_not_valid(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->setIdentifier('identifier1');
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn($violations);
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(new LegacyViolationsException($violations))->during('__invoke', [$command]);
    }

    function it_throws_an_exception_when_updater_throws_an_exception(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
        UserIntentApplierRegistry $applierRegistry,
        UserIntentApplier $userIntentApplier,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $setTextUserIntent = new SetTextValue('name', null, null, 'foo');
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: [$setTextUserIntent]);
        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);
        $applierRegistry->getApplier($setTextUserIntent)->willReturn($userIntentApplier);
        $userIntentApplier->apply($setTextUserIntent, $product, 1)->willThrow(
            InvalidPropertyException::expected('error', 'class')
        );
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$command]);
    }

    function it_updates_a_product_with_user_intents(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
        UserIntentApplierRegistry $applierRegistry,
        UserIntentApplier $applier,
        UserIntentApplier $applier2,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $userIntent = new SetEnabled(true);
        $setTextUserIntent = new SetTextValue('name', null, null, 'Lorem Ipsum');
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: [$userIntent, $setTextUserIntent]);
        $product = new Product();
        $product->setCreated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->setUpdated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productRepository->findOneByIdentifier('identifier1')->shouldBeCalledOnce()->willReturn($product);

        $applierRegistry->getApplier($userIntent)->shouldBeCalledOnce()->willReturn($applier);
        $applierRegistry->getApplier($setTextUserIntent)->shouldBeCalledOnce()->willReturn($applier2);
        $applier->apply($userIntent, $product, 1)->shouldBeCalledOnce();
        $applier2->apply($setTextUserIntent, $product, 1)->shouldBeCalledOnce();

        $productValidator->validate($product)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $productSaver->save($product)->shouldBeCalledOnce();
        $event = new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $eventDispatcher->dispatch($event)->shouldBeCalledOnce()->willReturn($event);

        $this->__invoke($command);
    }

    function it_throws_an_error_when_user_intent_cannot_be_handled(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
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
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            $unknownUserIntent
        ]);

        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $productRepository->findOneByIdentifier('identifier')->shouldBeCalledOnce()->willReturn($product);

        $productUpdater->update($product, Argument::cetera())->shouldNotbeCalled();
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$command]);
    }

    function it_throws_an_error_when_connected_user_is_different_from_user_id(
        ValidatorInterface $validator,
        SaverInterface $productSaver,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: []);

        $product = new Product();
        $product->setIdentifier('identifier1');

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(2);

        $productUpdater->update($product, Argument::cetera())->shouldNotbeCalled();
        $productValidator->validate($product)->shouldNotBeCalled();
        $productSaver->save($product)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->during('__invoke', [$command]);
    }
}
