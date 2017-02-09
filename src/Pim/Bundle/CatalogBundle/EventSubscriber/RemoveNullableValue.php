<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveNullableValue  implements EventSubscriberInterface
{
    /** @var RemoverInterface */
    protected $remover;

    /**
     * @param RemoverInterface $remover
     */
    public function __construct(RemoverInterface $remover)
    {
        $this->remover = $remover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'removeNullableValue',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function removeNullableValue(GenericEvent $event)
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface || null === $product->getFamily()) {
            return;
        }

        $attributes = $product->getFamily()->getAttributeCodes();
        foreach ($product->getValues() as $value) {
            if (null === $value->getData() && in_array($value->getAttribute()->getCode(), $attributes)) {
                $this->remover->remove($value);
            }
        }
    }
}
