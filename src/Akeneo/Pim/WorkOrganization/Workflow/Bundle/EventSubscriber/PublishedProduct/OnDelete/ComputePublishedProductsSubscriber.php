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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnDelete;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Deletes published products from the search engine
 */
final class ComputePublishedProductsSubscriber implements EventSubscriberInterface
{
    /** @var PublishedProductIndexer */
    private $publishedProductIndexer;

    public function __construct(PublishedProductIndexer $publishedProductIndexer)
    {
        $this->publishedProductIndexer = $publishedProductIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'deletePublishedProduct',
        ];
    }

    public function deletePublishedProduct(RemoveEvent $event): void
    {
        $publishedProduct = $event->getSubject();

        if ($publishedProduct instanceof PublishedProductInterface) {
            $this->publishedProductIndexer->remove($event->getSubjectId());
        }
    }
}
