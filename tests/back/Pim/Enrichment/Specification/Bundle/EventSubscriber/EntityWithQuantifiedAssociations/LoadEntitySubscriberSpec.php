<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations\LoadEntitySubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingAssociationTypeCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoadEntitySubscriberSpec extends ObjectBehavior
{
    function let(
        GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindNonExistingAssociationTypeCodesQueryInterface $findNonExistingAssociationTypeCodesQuery
    )
    {
        $this->beConstructedWith(
            $getIdMappingFromProductIds,
            $getIdMappingFromProductModelIds,
            $findNonExistingAssociationTypeCodesQuery
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoadEntitySubscriber::class);
    }

    function it_subscribes_to_the_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_loads_values_of_a_product(
        GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindNonExistingAssociationTypeCodesQueryInterface $findNonExistingAssociationTypeCodesQuery,
        LifecycleEventArgs $event,
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
    )
    {
        $event->getObject()->willReturn($entityWithQuantifiedAssociations);
        $associationTypeCodes = ['PACK'];
        $productIds = [1, 2];
        $productModelIds = [1, 2];
        $productIdMapping = $this->anIdMapping();
        $productModelIdMapping = $this->anIdMapping();

        $entityWithQuantifiedAssociations->getQuantifiedAssociationsTypeCodes()->willReturn($associationTypeCodes);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductIds()->willReturn($productIds);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductModelIds()->willReturn($productModelIds);
        $entityWithQuantifiedAssociations->hydrateQuantifiedAssociations($productIdMapping, $productModelIdMapping);

        $findNonExistingAssociationTypeCodesQuery->execute($associationTypeCodes)->willReturn([]);
        $getIdMappingFromProductIds->execute($productIds)->willReturn($productIdMapping);
        $getIdMappingFromProductModelIds->execute($productModelIds)->willReturn($productModelIdMapping);

        $this->postLoad($event);
    }

    function it_remove_non_existing_association_types_from_a_product(
        GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindNonExistingAssociationTypeCodesQueryInterface $findNonExistingAssociationTypeCodesQuery,
        LifecycleEventArgs $event,
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
    )
    {
        $event->getObject()->willReturn($entityWithQuantifiedAssociations);
        $associationTypeCodes = ['PACK', 'PRODUCT_SET'];
        $nonExistingAssociationTypeCodes = ['PACK'];
        $productIds = [];
        $productModelIds = [];
        $productIdMapping = $this->anIdMapping();
        $productModelIdMapping = $this->anIdMapping();

        $entityWithQuantifiedAssociations->getQuantifiedAssociationsTypeCodes()->willReturn($associationTypeCodes);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductIds()->willReturn([]);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductModelIds()->willReturn([]);
        $entityWithQuantifiedAssociations->hydrateQuantifiedAssociations($productIdMapping, $productModelIdMapping);

        $findNonExistingAssociationTypeCodesQuery->execute($associationTypeCodes)->willReturn($nonExistingAssociationTypeCodes);
        $getIdMappingFromProductIds->execute($productIds)->willReturn($productIdMapping);
        $getIdMappingFromProductModelIds->execute($productModelIds)->willReturn($productModelIdMapping);

        $entityWithQuantifiedAssociations->removeQuantifiedAssociationsType('PACK')->shouldBeCalled();

        $this->postLoad($event);
    }

    function it_ignores_non_entities_with_quantified_associations($getIdMappingFromProductIds, LifecycleEventArgs $event, \stdClass $randomEntity)
    {
        $event->getObject()->willReturn($randomEntity);
        $getIdMappingFromProductIds->execute(Argument::cetera())->shouldNotBeCalled();

        $this->postLoad($event);
    }

    private function anIdMapping(): IdMapping
    {
        return IdMapping::createFromMapping([1 => 'entity_1', 2 => 'entity_2']);
    }
}
