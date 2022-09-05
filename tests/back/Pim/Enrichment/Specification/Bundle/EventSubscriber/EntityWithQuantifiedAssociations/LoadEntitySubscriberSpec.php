<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations\LoadEntitySubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class LoadEntitySubscriberSpec extends ObjectBehavior
{
    function let(
        GetUuidMappingQueryInterface $getUuidMappingQuery,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    )
    {
        $this->beConstructedWith(
            $getUuidMappingQuery,
            $getIdMappingFromProductModelIds,
            $findQuantifiedAssociationTypeCodes
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
        GetUuidMappingQueryInterface $getUuidMappingQuery,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes,
        LifecycleEventArgs $event,
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
    )
    {
        $event->getObject()->willReturn($entityWithQuantifiedAssociations);
        $existingAssociationTypeCodes = ['PACK'];
        $productIds = [1, 2];
        $productUuids = [Uuid::uuid4(), Uuid::uuid4()];
        $productModelIds = [1, 2];
        $productUuidMapping = $this->aUuidMapping();
        $productModelIdMapping = $this->anIdMapping();

        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductIds()->willReturn($productIds);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductUuids()->willReturn($productUuids);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductModelIds()->willReturn($productModelIds);
        $entityWithQuantifiedAssociations->hydrateQuantifiedAssociations(
            $productUuidMapping,
            $productModelIdMapping,
            $existingAssociationTypeCodes
        )->shouldBeCalled();

        $findQuantifiedAssociationTypeCodes->execute()->willReturn($existingAssociationTypeCodes);
        $getUuidMappingQuery->fromProductIds($productIds, $productUuids)->willReturn($productUuidMapping);
        $getIdMappingFromProductModelIds->execute($productModelIds)->willReturn($productModelIdMapping);

        $this->postLoad($event);
    }

    function it_ignores_non_entities_with_quantified_associations(
        GetUuidMappingQueryInterface $getUuidMappingQuery,
        LifecycleEventArgs $event,
        \stdClass $randomEntity
    ) {
        $event->getObject()->willReturn($randomEntity);
        $getUuidMappingQuery->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();

        $this->postLoad($event);
    }

    private function anIdMapping(): IdMapping
    {
        return IdMapping::createFromMapping([1 => 'entity_1', 2 => 'entity_2']);
    }

    private function aUuidMapping(): UuidMapping
    {
        return UuidMapping::createFromMapping([
            ['uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'identifier' => 'entity_1', 'id' => 1],
            ['uuid' => '52254bba-a2c8-40bb-abe1-195e3970bd93', 'identifier' => 'entity_2', 'id' => 2],
        ]);
    }
}
