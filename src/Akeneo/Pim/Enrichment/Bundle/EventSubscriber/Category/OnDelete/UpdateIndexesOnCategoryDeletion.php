<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\GetDescendentCategoryCodes;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscribes to category deletion events and updates all ES indexes accordingly.
 *
 * This subscriber has to be stateful for consistency : we need to get all descendent codes before the category is
 * effectively removed (and the nested set is reindexed), but we also need to not perform the ES indexes update if the
 * removal fails (because of a database constraint for example).
 *
 * Ideally this should be done by working more with domain events and aggregates, but as we don't have this for now and
 * we heavily rely on database constraints (cascade delete in this case) it's hard to find a clean solution.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateIndexesOnCategoryDeletion implements EventSubscriberInterface
{
    /** @var GetDescendentCategoryCodes */
    private $getDescendentCategoryCodes;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var string[] */
    private $categoryCodesToRemove = [];

    public function __construct(
        GetDescendentCategoryCodes $getDescendentCategoryCodes,
        Client $productAndProductModelClient
    ) {
        $this->getDescendentCategoryCodes = $getDescendentCategoryCodes;
        $this->productAndProductModelClient = $productAndProductModelClient;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE  => 'storeCategoryCodesToRemove',
            StorageEvents::POST_REMOVE => 'updateIndexes',
        ];
    }

    public function storeCategoryCodesToRemove(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof CategoryInterface) {
            return;
        }

        $getDescendentCategoryCodes = $this->getDescendentCategoryCodes;
        $parentCategory = $event->getSubject();

        $this->categoryCodesToRemove = $getDescendentCategoryCodes($parentCategory);
        $this->categoryCodesToRemove[] = $parentCategory->getCode();
    }

    public function updateIndexes(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof CategoryInterface) {
            return;
        }

        $body = [
            'query' => [
                'terms' => ['categories' => $this->categoryCodesToRemove],
            ],
            'script' => [
                // WARNING: "inline" will need to be changed to "source" when we'll switch to Elasticsearch 5.6
                'inline' => 'ctx._source.categories.removeAll(params.categories); if (0 == ctx._source.categories.size()) { ctx._source.remove("categories"); }',
                'lang'   => 'painless',
                'params' => ['categories' => $this->categoryCodesToRemove],
            ],
        ];

        $this->productAndProductModelClient->updateByQuery($body);
    }
}
