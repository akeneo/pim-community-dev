<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\TwoWayProductAssociationsSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveTwoWayProductAssociations implements EventSubscriberInterface
{
    private $twoWayProductAssociationsSaver;

    public function __construct(TwoWayProductAssociationsSaver $twoWayProductAssociationsSaver)
    {
        $this->twoWayProductAssociationsSaver = $twoWayProductAssociationsSaver;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'saveInvertedAssociations'
        ];
    }

    public function saveInvertedAssociations(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $twoWayAssociations = array_filter($product->getAssociations()->toArray(), function (AssociationInterface $association) {
            return $association->getAssociationType()->isTwoWay();
        });

        if (empty($twoWayAssociations)) {
            return;
        }

        $this->twoWayProductAssociationsSaver->saveInvertedAssociations($product, array_values($twoWayAssociations));
    }
}
