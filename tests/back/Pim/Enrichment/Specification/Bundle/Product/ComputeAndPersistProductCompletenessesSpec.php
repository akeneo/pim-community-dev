<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection;
use Akeneo\Pim\Enrichment\Product\Domain\Clock;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenessesSpec extends ObjectBehavior
{
    public function let(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(
            $completenessCalculator,
            $saveProductCompletenesses,
            $getProductCompletenesses,
            $eventDispatcher,
            $clock,
            $tokenStorage,
            $logger
        );
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType(ComputeAndPersistProductCompletenesses::class);
    }

    public function it_dispatches_event_when_products_have_been_completed(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);

        $getProductCompletenesses->fromProductUuids([
            $uuid1->toString(),
            $uuid2->toString(),
        ])->willReturn(
            [
                $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 6),
                    new ProductCompleteness('ecommerce', 'en_US', 10, 0),
                ]),
                $uuid2->toString() => new ProductCompletenessCollection($uuid2, [
                    new ProductCompleteness('mobile', 'fr_FR', 10, 8),
                    new ProductCompleteness('ecommerce', 'en_US', 10, 1),
                ]),
            ]
        );

        $newProductsCompletenesses = [
            $uuid1->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid1->toString(), [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 10, []),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
            ]),
            $uuid2->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid2->toString(), [
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'fr_FR', 10, ['name', 'title', 'short_title', 'weight', 'length']),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
            ]),
        ];

        $completenessCalculator->fromProductUuids([$uuid1->toString(), $uuid2->toString()])
            ->shouldBeCalled()->willReturn($newProductsCompletenesses);

        $saveProductCompletenesses->saveAll($newProductsCompletenesses)->shouldBeCalledOnce();

        $changedAt = new \DateTimeImmutable('2022-10-01');
        $clock->now()->willReturn($changedAt);

        $event = new ProductWasCompletedOnChannelLocaleCollection([
            new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid($uuid1), $changedAt, 'ecommerce', 'fr_FR', '1'),
            new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid($uuid2), $changedAt, 'ecommerce', 'en_US', '1'),
        ]);

        $eventDispatcher->dispatch($event)->shouldBeCalledOnce();

        $this->fromProductUuids([
            $uuid1->toString(),
            $uuid2->toString(),
        ]);
    }

    public function it_doesnt_dispatch_event_when_products_have_not_been_completed(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
    ) {
        $uuid1 = Uuid::uuid4();

        $getProductCompletenesses->fromProductUuids([
            $uuid1->toString(),
        ])->willReturn(
            [
                $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 2),
                ]),
            ]
        );

        $newProductsCompletenesses = [
            $uuid1->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid1->toString(), [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 10, ['name', 'title']),
            ]),
        ];

        $completenessCalculator->fromProductUuids([$uuid1->toString()])->willReturn($newProductsCompletenesses);
        $saveProductCompletenesses->saveAll($newProductsCompletenesses)->shouldBeCalledOnce();

        $changedAt = new \DateTimeImmutable('2022-10-01');
        $clock->now()->willReturn($changedAt);

        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->fromProductUuids([$uuid1->toString()]);
    }

    function it_logs_exception_and_doesnt_crash_when_dispatching_error_happens(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LoggerInterface $logger
    ) {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);

        $getProductCompletenesses->fromProductUuids([
            $uuid1->toString(),
            $uuid2->toString(),
        ])->willReturn([
            $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                new ProductCompleteness('ecommerce', 'fr_FR', 10, 6),
                new ProductCompleteness('ecommerce', 'en_US', 10, 0),
            ]),
            $uuid2->toString() => new ProductCompletenessCollection($uuid2, [
                new ProductCompleteness('mobile', 'fr_FR', 10, 8),
                new ProductCompleteness('ecommerce', 'en_US', 10, 1),
            ]),
        ]);

        $newProductsCompletenesses = [
            $uuid1->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid1->toString(), [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 10, []),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
            ]),
            $uuid2->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid2->toString(), [
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'fr_FR', 10, ['name', 'title', 'short_title', 'weight', 'length']),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
            ]),
        ];

        $completenessCalculator->fromProductUuids([$uuid1->toString(), $uuid2->toString()])
            ->shouldBeCalled()->willReturn($newProductsCompletenesses);

        $saveProductCompletenesses->saveAll($newProductsCompletenesses)->shouldBeCalledOnce();

        $changedAt = new \DateTimeImmutable('2022-10-01');
        $clock->now()->willReturn($changedAt);

        $event = new ProductWasCompletedOnChannelLocaleCollection([
            new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid($uuid1), $changedAt, 'ecommerce', 'fr_FR', '1'),
            new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid($uuid2), $changedAt, 'ecommerce', 'en_US', '1'),
        ]);

        $error = new \TypeError();
        $eventDispatcher->dispatch($event)->willThrow($error);
        $logger->error('Error while dispatching ProductWasCompletedOnChannelLocaleCollection event', ['exception' => $error])
            ->shouldBeCalledOnce();

        $this->fromProductUuids([$uuid1->toString(), $uuid2->toString()]);
    }
}
