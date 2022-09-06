<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations\ComputeEntitySubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeEntitySubscriberSpec extends ObjectBehavior
{
    function let(
        GetUuidMappingQueryInterface                    $getUuidMappingFromProductIdentifiers,
        GetIdMappingFromProductModelCodesQueryInterface $getIdMappingFromProductModelCodes
    ) {
        $this->beConstructedWith(
            $getUuidMappingFromProductIdentifiers,
            $getIdMappingFromProductModelCodes
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeEntitySubscriber::class);
    }

    function it_subscribes_to_pre_save_event()
    {
        $subscribed = $this->getSubscribedEvents();
        $subscribed->shouldHaveKey(StorageEvents::PRE_SAVE);
        $subscribed->shouldHaveCount(1);
    }

    function it_computes_quantified_associations(
        GetUuidMappingQueryInterface                    $getUuidMappingFromProductIdentifiers,
        GetIdMappingFromProductModelCodesQueryInterface $getIdMappingFromProductModelCodes,
        GenericEvent                                    $event,
        EntityWithQuantifiedAssociationsInterface       $entityWithQuantifiedAssociations
    ) {
        $event->getSubject()->willReturn($entityWithQuantifiedAssociations);
        $productModelCodes = ['product_1'];
        $productIdentifiers = ['product_model_1', 'product_model_2'];
        $productUuids = ['52254bba-a2c8-40bb-abe1-195e3970bd93'];
        $productUuidMapping = $this->aUuidMapping();
        $productModelIdMapping = $this->anIdMapping();

        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductIdentifiers()->willReturn($productIdentifiers);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductUuids()->willReturn($productUuids);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductModelCodes()->willReturn($productModelCodes);
        $entityWithQuantifiedAssociations
            ->updateRawQuantifiedAssociations($productUuidMapping, $productModelIdMapping)
            ->shouldBeCalledOnce();

        $getUuidMappingFromProductIdentifiers->fromProductIdentifiers($productIdentifiers, $productUuids)->willReturn($productUuidMapping);
        $getIdMappingFromProductModelCodes->execute($productModelCodes)->willReturn($productModelIdMapping);

        $this->computeRawQuantifiedAssociations($event);
    }

    function it_ignores_non_entities_with_quantified_associations(
        GetUuidMappingQueryInterface $getUuidMappingFromProductIdentifiersQuery,
        GenericEvent                 $event,
        \stdClass                    $randomEntity
    ) {
        $event->getSubject()->willReturn($randomEntity);
        $getUuidMappingFromProductIdentifiersQuery->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();

        $this->computeRawQuantifiedAssociations($event);
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
