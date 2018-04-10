<?php

declare(strict_types=1);

namespace Pim\Bundle\ConnectorBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Clear the internal cache between each batch during the execution of an item step.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearBatchCacheSubscriber implements EventSubscriberInterface
{
    /** @var CacheClearerInterface */
    private $cacheClearer;

    /**
     * @param CacheClearerInterface $cacheClearer
     */
    public function __construct(CacheClearerInterface $cacheClearer)
    {
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::ITEM_STEP_AFTER_BATCH => 'clearCache',
        ];
    }

    public function clearCache(): void
    {
        $this->cacheClearer->clear();
    }
}
