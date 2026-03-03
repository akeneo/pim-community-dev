<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Computes the raw quantified association from the QuantifiedAssociation VO,
 * so that doctrine is able to persist the changes in DB.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeEntitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected GetUuidMappingQueryInterface $getUuidMappingQuery,
        protected GetIdMappingFromProductModelCodesQueryInterface $getIdMappingFromProductModelCodes
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_SAVE => 'computeRawQuantifiedAssociations'];
    }

    /**
     * Normalizes product values into "storage" format, and sets the result as raw values.
     *
     * @param GenericEvent $event
     */
    public function computeRawQuantifiedAssociations(GenericEvent $event)
    {
        $subject = $event->getSubject();
        if (!$subject instanceof EntityWithQuantifiedAssociationsInterface) {
            return;
        }

        $productIdentifiers = $subject->getQuantifiedAssociationsProductIdentifiers();
        $productUuids = $subject->getQuantifiedAssociationsProductUuids();
        $productModelCodes = $subject->getQuantifiedAssociationsProductModelCodes();

        $uuidMappedProductIdentifiers = $this->getUuidMappingQuery->fromProductIdentifiers($productIdentifiers, $productUuids);
        $mappedProductModelCodes = $this->getIdMappingFromProductModelCodes->execute($productModelCodes);

        $subject->updateRawQuantifiedAssociations(
            $uuidMappedProductIdentifiers,
            $mappedProductModelCodes
        );
    }
}
