<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ChangedProductCompleteness;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductCompletenessCollectionWasChanged;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductsCompletenessCollectionsWereChanged;
use Akeneo\Pim\Enrichment\Product\Domain\Clock;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
    ) {
        $this->beConstructedWith(
            $completenessCalculator,
            $saveProductCompletenesses,
            $getProductCompletenesses,
            $eventDispatcher,
            $clock
        );
    }

    public function it_is_initialize()
    {
        $this->shouldHaveType(ComputeAndPersistProductCompletenesses::class);
    }

    public function it_dispatches_event_when_products_completeness_have_changed(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
    ){
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();

        $getProductCompletenesses->fromProductUuids([
            $uuid1->toString(),
            $uuid2->toString(),
            $uuid3->toString(),
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
                $uuid3->toString() => new ProductCompletenessCollection($uuid3, [
                    new ProductCompleteness('mobile', 'fr_FR', 10, 8),
                ]),
            ]
        );

        $newProductsCompletenesses = [
            $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                new ProductCompleteness('ecommerce', 'fr_FR', 10, 0),
                new ProductCompleteness('ecommerce', 'en_US', 10, 0),
            ]),
            $uuid2->toString() => new ProductCompletenessCollection($uuid2, [
                new ProductCompleteness('mobile', 'fr_FR', 10, 5),
                new ProductCompleteness('ecommerce', 'en_US', 10, 0),
            ]),
            $uuid3->toString() => new ProductCompletenessCollection($uuid3, [
                new ProductCompleteness('mobile', 'fr_FR', 10, 8),
            ]),
        ];

        $completenessCalculator->fromProductUuids([
            $uuid1->toString(),
            $uuid2->toString(),
            $uuid3->toString()
        ])->shouldBeCalled()->willReturn(
            $newProductsCompletenesses
        );

        $saveProductCompletenesses->saveAll($newProductsCompletenesses)->shouldBeCalledOnce();

        $changedAt = new \DateTimeImmutable('2022-10-01');
        $clock->now()->willReturn($changedAt);

        $event = new ProductsCompletenessCollectionsWereChanged([
            new ProductCompletenessCollectionWasChanged(
                ProductUuid::fromUuid($uuid1),
                $changedAt,
                [
                    new ChangedProductCompleteness('ecommerce', 'fr_FR', 10, 10, 6, 0, 40, 100),
                ]
            ),
            new ProductCompletenessCollectionWasChanged(
                ProductUuid::fromUuid($uuid2),
                $changedAt,
                [
                    new ChangedProductCompleteness('mobile', 'fr_FR', 10, 10, 8, 5, 20, 50),
                    new ChangedProductCompleteness('ecommerce', 'en_US', 10, 10, 1, 0, 90, 100),
                ]
            )
        ]);

        $eventDispatcher->dispatch($event)->shouldBeCalledOnce();

        $this->fromProductUuids([
            $uuid1->toString(),
            $uuid2->toString(),
            $uuid3->toString()
        ]);
    }

    public function it_doesnt_dispatches_event_when_products_completeness_have_not_changed(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
    ){
        $uuid1 = Uuid::uuid4();

        $getProductCompletenesses->fromProductUuids([
            $uuid1->toString(),
        ])->willReturn(
            [
                $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 6),
                ]),
            ]
        );

        $newProductsCompletenesses = [
            $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                new ProductCompleteness('ecommerce', 'fr_FR', 10, 6),
            ]),
        ];

        $completenessCalculator->fromProductUuids([$uuid1->toString()])->willReturn($newProductsCompletenesses);
        $saveProductCompletenesses->saveAll($newProductsCompletenesses)->shouldBeCalledOnce();

        $changedAt = new \DateTimeImmutable('2022-10-01');
        $clock->now()->willReturn($changedAt);

        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->fromProductUuids([$uuid1->toString()]);
    }
}
