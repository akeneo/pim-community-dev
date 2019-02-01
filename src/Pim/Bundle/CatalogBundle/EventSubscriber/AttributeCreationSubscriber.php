<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * At the creation of an attribute force the attribute sort order.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCreationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'incrementSortOrder'
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function incrementSortOrder(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface || null !== $attribute->getId()) {
            return;
        }

        $attribute->setSortOrder($attribute->getGroup()->getMaxAttributeSortOrder() + 1);
    }
}
