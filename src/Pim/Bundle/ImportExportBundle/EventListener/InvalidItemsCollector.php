<?php

namespace Pim\Bundle\ImportExportBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Event\InvalidItemEvent;

/**
 * Collect invalid items
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemsCollector implements EventSubscriberInterface
{
    /** @var array */
    protected $invalidItems;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::INVALID_ITEM => 'collect'
        );
    }

    public function collect(InvalidItemEvent $event)
    {
        $this->invalidItems[$this->getHashKey($event->getItem())] = $event->getItem();
    }

    public function getInvalidItems()
    {
        return $this->invalidItems;
    }

    protected function getHashKey(array $item)
    {
        return md5(serialize($item));
    }
}
