<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\Association;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\TwoWayProductModelAssociationsSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveTwoWayProductModelAssociations implements EventSubscriberInterface
{
    private $twoWayProductModelAssociationsSaver;

    public function __construct(TwoWayProductModelAssociationsSaver $twoWayProductModelAssociationsSaver)
    {
        $this->twoWayProductModelAssociationsSaver = $twoWayProductModelAssociationsSaver;
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
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        $twoWayAssociations = array_filter($productModel->getAssociations()->toArray(), function (AssociationInterface $association) {
            return $association->getAssociationType()->isTwoWay();
        });

        if (empty($twoWayAssociations)) {
            return;
        }

        $this->twoWayProductModelAssociationsSaver->saveInvertedAssociations($productModel, array_values($twoWayAssociations));
    }
}
