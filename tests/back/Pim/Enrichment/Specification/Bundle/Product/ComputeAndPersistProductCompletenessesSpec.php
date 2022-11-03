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
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductCompletenessWasChanged;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductsCompletenessWereChanged;
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

    public function it_can_be_initialized()
    {
        $this->shouldHaveType(ComputeAndPersistProductCompletenesses::class);
    }

    public function it_dispatches_event_when_products_completeness_have_changed(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        EventDispatcher $eventDispatcher,
        Clock $clock,
    ) {
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
                    new ProductCompleteness('mobile', 'fr_FR', 10, 2),
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
            $uuid3->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid3->toString(), [
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'fr_FR', 10, ['name', 'title']),
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

        $event = new ProductsCompletenessWereChanged([
            new ProductCompletenessWasChanged(
                ProductUuid::fromUuid($uuid1), $changedAt, 'ecommerce', 'fr_FR', 10, 10, 6, 0, 40, 100
            ),
            new ProductCompletenessWasChanged(
                ProductUuid::fromUuid($uuid2), $changedAt, 'mobile', 'fr_FR', 10, 10, 8, 5, 20, 50
            ),
            new ProductCompletenessWasChanged(
                ProductUuid::fromUuid($uuid2), $changedAt, 'ecommerce', 'en_US', 10, 10, 1, 0, 90, 100
            ),
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
}
