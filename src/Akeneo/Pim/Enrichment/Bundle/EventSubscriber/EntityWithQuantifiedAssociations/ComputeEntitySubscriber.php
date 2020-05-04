<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
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
    /** @var GetIdMappingFromProductIdentifiersQueryInterface */
    protected $getIdMappingFromProductIdentifiers;

    /** @var GetIdMappingFromProductModelCodesQueryInterface */
    protected $getIdMappingFromProductModelCodes;

    /**
     * @param GetIdMappingFromProductIdentifiersQueryInterface $getIdMappingFromProductIdentifiers
     * @param GetIdMappingFromProductModelCodesQueryInterface $getIdMappingFromProductModelCodes
     */
    public function __construct(
        GetIdMappingFromProductIdentifiersQueryInterface $getIdMappingFromProductIdentifiers,
        GetIdMappingFromProductModelCodesQueryInterface $getIdMappingFromProductModelCodes
    ) {
        $this->getIdMappingFromProductIdentifiers = $getIdMappingFromProductIdentifiers;
        $this->getIdMappingFromProductModelCodes = $getIdMappingFromProductModelCodes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
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
        $productModelCodes = $subject->getQuantifiedAssociationsProductModelCodes();

        $mappedProductIdentifiers = $this->getIdMappingFromProductIdentifiers->execute($productIdentifiers);
        $mappedProductModelCodes = $this->getIdMappingFromProductModelCodes->execute($productModelCodes);

        $subject->updateRawQuantifiedAssociations($mappedProductIdentifiers, $mappedProductModelCodes);
    }
}
