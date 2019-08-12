<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ComputeAndPersistPublishedProductCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var SavePublishedProductCompletenesses */
    private $savePublishedProductCompletenesses;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    public function __construct(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->savePublishedProductCompletenesses = $savePublishedProductCompletenesses;
        $this->getProductCompletenesses = $getProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['computePublishedProductCompleteness', 320],
        ];
    }

    public function computePublishedProductCompleteness(GenericEvent $event): void
    {
        $publishedProduct = $event->getSubject();

        if (!$publishedProduct instanceof PublishedProductInterface) {
            return;
        }

        $originalProductId = $publishedProduct->getOriginalProduct()->getId();
        $originalProductCompletenesses = $this->getProductCompletenesses->fromProductId($originalProductId);

        $publishedProductCompletenesses = array_map(function (ProductCompleteness $productCompleteness): PublishedProductCompleteness {
            return new PublishedProductCompleteness(
                $productCompleteness->channelCode(),
                $productCompleteness->localeCode(),
                $productCompleteness->requiredCount(),
                $productCompleteness->missingAttributeCodes()
            );
        }, iterator_to_array($originalProductCompletenesses));

        $collection = new PublishedProductCompletenessCollection($publishedProduct->getId(), $publishedProductCompletenesses);

        $this->savePublishedProductCompletenesses->save($collection);
    }
}
