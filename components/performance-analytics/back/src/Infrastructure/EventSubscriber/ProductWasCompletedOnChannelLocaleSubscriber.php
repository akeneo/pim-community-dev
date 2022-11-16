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

namespace Akeneo\PerformanceAnalytics\Infrastructure\EventSubscriber;

use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnriched;
use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnrichedHandler;
use Akeneo\PerformanceAnalytics\Application\Command\ProductIsEnriched;
use Akeneo\PerformanceAnalytics\Application\LogContext;
use Akeneo\PerformanceAnalytics\Domain\Product\ChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProductWasCompletedOnChannelLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotifyProductsAreEnrichedHandler $notifyProductsAreEnrichedHandler,
        private LoggerInterface $logger,
        private FeatureFlag $notifyEnrichedProductsFeature,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductWasCompletedOnChannelLocaleCollection::class => 'onProductWasCompletedOnChannelLocaleCollection',
        ];
    }

    public function onProductWasCompletedOnChannelLocaleCollection(ProductWasCompletedOnChannelLocaleCollection $event): void
    {
        if (!$this->notifyEnrichedProductsFeature->isEnabled()) {
            return;
        }

        try {
            $command = $this->buildCommand($event);

            if (null !== $command) {
                ($this->notifyProductsAreEnrichedHandler)($command);
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), LogContext::build(['exception' => $exception]));
        }
    }

    private function buildCommand(ProductWasCompletedOnChannelLocaleCollection $productWasCompletedOnChannelLocaleCollection): ?NotifyProductsAreEnriched
    {
        $enrichedProductsCompletenesses = [];

        /** @var ProductWasCompletedOnChannelLocale $productWasCompletedOnChannelLocale */
        foreach ($productWasCompletedOnChannelLocaleCollection->all() as $productWasCompletedOnChannelLocale) {
            $uuidString = $productWasCompletedOnChannelLocale->productUuid()->uuid()->toString();

            $enrichedChannelLocale = ChannelLocale::fromChannelAndLocaleString(
                $productWasCompletedOnChannelLocale->channelCode(),
                $productWasCompletedOnChannelLocale->localeCode()
            );

            // @todo Rework ProductIsEnriched to have one instance by product/channel/locale (JEL-79)
            $enrichedChannelsLocales = \array_key_exists($uuidString, $enrichedProductsCompletenesses)
                ? [...$enrichedProductsCompletenesses[$uuidString]->channelsLocales(), $enrichedChannelLocale]
                : [$enrichedChannelLocale];

            $enrichedProductsCompletenesses[$uuidString] = new ProductIsEnriched(
                $productWasCompletedOnChannelLocale->productUuid()->uuid(),
                $enrichedChannelsLocales,
                $productWasCompletedOnChannelLocale->completedAt()
            );
        }

        return [] !== $enrichedProductsCompletenesses
            ? new NotifyProductsAreEnriched(\array_values($enrichedProductsCompletenesses))
            : null;
    }
}
