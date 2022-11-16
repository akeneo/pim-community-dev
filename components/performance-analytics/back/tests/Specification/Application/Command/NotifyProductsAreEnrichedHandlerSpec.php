<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Application\Command;

use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnriched;
use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnrichedHandler;
use Akeneo\PerformanceAnalytics\Application\Command\ProductIsEnriched;
use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\MessageQueue;
use Akeneo\PerformanceAnalytics\Domain\Product\GetProducts;
use Akeneo\PerformanceAnalytics\Domain\Product\Product;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductWasEnrichedMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class NotifyProductsAreEnrichedHandlerSpec extends ObjectBehavior
{
    public function let(
        MessageQueue $messageQueue,
        GetProducts $getProducts,
        LoggerInterface $logger,
    ) {
        $this->beConstructedWith($messageQueue, $getProducts, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(NotifyProductsAreEnrichedHandler::class);
    }

    public function it_notifies_products_are_enriched(
        MessageQueue $messageQueue,
        GetProducts $getProducts,
    ) {
        $productUuid1 = Uuid::uuid4();
        $productUuid2 = Uuid::uuid4();
        $enrichedAt = new \DateTimeImmutable('2022-08-22');

        $product1 = Product::fromProperties(
            $productUuid1,
            new \DateTimeImmutable('2022-07-01'),
            FamilyCode::fromString('family1'),
            [
                CategoryCode::fromString('category1'),
                CategoryCode::fromString('category2'),
            ]
        );

        $product2 = Product::fromProperties(
            $productUuid2,
            new \DateTimeImmutable('2022-06-01'),
            FamilyCode::fromString('family2'),
            [
                CategoryCode::fromString('category3'),
                CategoryCode::fromString('category4'),
            ]
        );

        $getProducts->byUuids([$productUuid1, $productUuid2])->willReturn([
            $productUuid1->toString() => $product1,
            $productUuid2->toString() => $product2,
        ]);

        $productWasEnrichedList = [
            ProductWasEnrichedMessage::fromProperties(
                $product1,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('fr_FR'),
                $enrichedAt,
            ),
            ProductWasEnrichedMessage::fromProperties(
                $product1,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('en_US'),
                $enrichedAt,
            ),
            ProductWasEnrichedMessage::fromProperties(
                $product2,
                ChannelCode::fromString('mobile'),
                LocaleCode::fromString('en_US'),
                $enrichedAt,
            ),
        ];

        $messageQueue->publishBatch($productWasEnrichedList)->shouldBeCalled();

        $this->__invoke(new NotifyProductsAreEnriched([
            new ProductIsEnriched(
                $productUuid1,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('fr_FR'),
                $enrichedAt
            ),
            new ProductIsEnriched(
                $productUuid1,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('en_US'),
                $enrichedAt
            ),
            new ProductIsEnriched(
                $productUuid2,
                ChannelCode::fromString('mobile'),
                LocaleCode::fromString('en_US'),
                $enrichedAt
            ),
        ]));
    }

    public function it_does_not_notify_unknown_product(
        MessageQueue $messageQueue,
        GetProducts $getProducts,
        LoggerInterface $logger,
    ) {
        $productUuid1 = Uuid::uuid4();
        $productUuid2 = Uuid::uuid4();
        $enrichedAt = new \DateTimeImmutable('2022-08-22');

        $product1 = Product::fromProperties(
            $productUuid1,
            new \DateTimeImmutable('2022-07-01'),
            FamilyCode::fromString('family1'),
            [
                CategoryCode::fromString('category1'),
                CategoryCode::fromString('category2'),
            ]
        );

        $getProducts->byUuids([$productUuid1->toString(), $productUuid2->toString()])->willReturn([
            $productUuid1->toString() => $product1,
        ]);

        $productWasEnrichedList = [
            ProductWasEnrichedMessage::fromProperties(
                $product1,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('fr_FR'),
                $enrichedAt,
            ),
        ];

        $messageQueue->publishBatch($productWasEnrichedList)->shouldBeCalled();
        $logger->warning(Argument::cetera())->shouldBeCalled();

        $this->__invoke(new NotifyProductsAreEnriched([
            new ProductIsEnriched(
                $productUuid1,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('fr_FR'),
                $enrichedAt
            ),
            new ProductIsEnriched(
                $productUuid2,
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('en_GB'),
                $enrichedAt
            ),
        ]));
    }
}
