<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingAssociationTypeCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Load quantified associations into a QuantifiedAssociation Value Object.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
final class LoadEntitySubscriber implements EventSubscriber
{
    /** @var GetIdMappingFromProductIdsQueryInterface */
    private $getIdMappingFromProductIds;

    /** @var GetIdMappingFromProductModelIdsQueryInterface */
    private $getIdMappingFromProductModelIds;

    /** @var FindNonExistingAssociationTypeCodesQueryInterface */
    private $findNonExistingAssociationTypeCodesQuery;

    public function __construct(
        GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindNonExistingAssociationTypeCodesQueryInterface $findNonExistingAssociationTypeCodesQuery
    ) {
        $this->getIdMappingFromProductIds = $getIdMappingFromProductIds;
        $this->getIdMappingFromProductModelIds = $getIdMappingFromProductModelIds;
        $this->findNonExistingAssociationTypeCodesQuery = $findNonExistingAssociationTypeCodesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
        ];
    }

    /**
     * Here we load the real object values from the raw values field.
     *
     * For products, we also add the identifier as a regular value
     * so that it can be used in the product edit form transparently.
     *
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityWithQuantifiedAssociationsInterface) {
            return;
        }

        $this->removeNonExistingQuantifiedAssociationsTypes($entity);

        $productIds = $entity->getQuantifiedAssociationsProductIds();
        $productModelIds = $entity->getQuantifiedAssociationsProductModelIds();

        $mappedProductIds = $this->getIdMappingFromProductIds->execute($productIds);
        $mappedProductModelIds = $this->getIdMappingFromProductModelIds->execute($productModelIds);

        $entity->hydrateQuantifiedAssociations($mappedProductIds, $mappedProductModelIds);
    }

    private function removeNonExistingQuantifiedAssociationsTypes(
        EntityWithQuantifiedAssociationsInterface $entity
    ): void {
        $currentQuantifiedAssociationsTypeCodes = $entity->getQuantifiedAssociationsTypeCodes();

        $nonExistingQuantifiedAssociationsTypeCodes = $this->findNonExistingAssociationTypeCodesQuery->execute(
            $currentQuantifiedAssociationsTypeCodes
        );

        foreach ($nonExistingQuantifiedAssociationsTypeCodes as $nonExistingQuantifiedAssociationsTypeCode) {
            $entity->removeQuantifiedAssociationsType($nonExistingQuantifiedAssociationsTypeCode);
        }
    }
}
