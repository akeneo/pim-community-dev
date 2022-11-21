<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Infrastructure\EventSubscriber;

use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnriched;
use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnrichedHandler;
use Akeneo\PerformanceAnalytics\Application\Command\ProductIsEnriched;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Infrastructure\EventSubscriber\ProductWasCompletedOnChannelLocaleSubscriber;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class ProductWasCompletedOnChannelLocaleSubscriberSpec extends ObjectBehavior
{
    public function let(
        NotifyProductsAreEnrichedHandler $notifyProductsAreEnrichedHandler,
        LoggerInterface $logger,
        FeatureFlag $notifyEnrichedProductsFeature,
    ) {
        $notifyEnrichedProductsFeature->isEnabled()->willReturn(true);
        $this->beConstructedWith($notifyProductsAreEnrichedHandler, $logger, $notifyEnrichedProductsFeature);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductWasCompletedOnChannelLocaleSubscriber::class);
    }

    public function it_notifies_products_were_enriched_when_products_were_completed(
        NotifyProductsAreEnrichedHandler $notifyProductsAreEnrichedHandler
    ) {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $productUuid1 = ProductUuid::fromUuid($uuid1);
        $productUuid2 = ProductUuid::fromUuid($uuid2);

        $completedAt = new \DateTimeImmutable('2022-01-15 12:09:34');

        $event = new ProductWasCompletedOnChannelLocaleCollection([
            new ProductWasCompletedOnChannelLocale($productUuid1, $completedAt, 'ecommerce', 'en_US', '1'),
            new ProductWasCompletedOnChannelLocale($productUuid1, $completedAt, 'mobile', 'en_US', '1'),

            new ProductWasCompletedOnChannelLocale($productUuid2, $completedAt, 'ecommerce', 'en_US', '1'),
        ]);

        $notifyProductsAreEnrichedHandler->__invoke(
            new NotifyProductsAreEnriched([
                new ProductIsEnriched(
                    $uuid1,
                    ChannelCode::fromString('ecommerce'),
                    LocaleCode::fromString('en_US'),
                    $completedAt,
                    '1'
                ),
                new ProductIsEnriched(
                    $uuid1,
                    ChannelCode::fromString('mobile'),
                    LocaleCode::fromString('en_US'),
                    $completedAt,
                    '1'
                ),
                new ProductIsEnriched(
                    $uuid2,
                    ChannelCode::fromString('ecommerce'),
                    LocaleCode::fromString('en_US'),
                    $completedAt,
                    '1'
                ),
            ])
        )->shouldBeCalledOnce();

        $this->onProductWasCompletedOnChannelLocaleCollection($event);
    }

    public function it_does_crash_if_an_exception_is_thrown_during_notification(
        NotifyProductsAreEnrichedHandler $notifyProductsAreEnrichedHandler,
        LoggerInterface $logger
    ) {
        $event = new ProductWasCompletedOnChannelLocaleCollection([
            new ProductWasCompletedOnChannelLocale(
                ProductUuid::fromUuid(Uuid::uuid4()),
                new \DateTimeImmutable('2022-01-15 12:09:34'),
                'ecommerce',
                'en_US',
                '1'
            ),
        ]);

        $notifyProductsAreEnrichedHandler->__invoke(Argument::any())->willThrow(new \Exception());

        $logger->error(Argument::cetera())->shouldBeCalled();

        $this->onProductWasCompletedOnChannelLocaleCollection($event);
    }

    public function it_does_nothing_if_the_feature_is_disabled(
        NotifyProductsAreEnrichedHandler $notifyProductsAreEnrichedHandler,
        FeatureFlag $notifyEnrichedProductsFeature
    ) {
        $notifyEnrichedProductsFeature->isEnabled()->willReturn(false);

        $notifyProductsAreEnrichedHandler->__invoke(Argument::any())->shouldNotBeCalled();

        $eventWithNewEnrichedProduct = new ProductWasCompletedOnChannelLocaleCollection([
            new ProductWasCompletedOnChannelLocale(
                ProductUuid::fromUuid(Uuid::uuid4()),
                new \DateTimeImmutable('2022-01-15 12:09:34'),
                'ecommerce',
                'en_US',
                '1'
            ),
        ]);

        $this->onProductWasCompletedOnChannelLocaleCollection($eventWithNewEnrichedProduct);
    }
}
