<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations\LoadEntitySubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoadEntitySubscriberSpec extends ObjectBehavior
{
    function let(
        GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    )
    {
        $this->beConstructedWith(
            $getIdMappingFromProductIds,
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
        GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
        GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes,
        LifecycleEventArgs $event,
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
    )
    {
        $event->getObject()->willReturn($entityWithQuantifiedAssociations);
        $existingAssociationTypeCodes = ['PACK'];
        $productIds = [1, 2];
        $productModelIds = [1, 2];
        $productIdMapping = $this->anIdMapping();
        $productModelIdMapping = $this->anIdMapping();

        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductIds()->willReturn($productIds);
        $entityWithQuantifiedAssociations->getQuantifiedAssociationsProductModelIds()->willReturn($productModelIds);
        $entityWithQuantifiedAssociations->hydrateQuantifiedAssociations(
            $productIdMapping,
            $productModelIdMapping,
            $existingAssociationTypeCodes
        )->shouldBeCalled();

        $findQuantifiedAssociationTypeCodes->execute()->willReturn($existingAssociationTypeCodes);
        $getIdMappingFromProductIds->execute($productIds)->willReturn($productIdMapping);
        $getIdMappingFromProductModelIds->execute($productModelIds)->willReturn($productModelIdMapping);

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
