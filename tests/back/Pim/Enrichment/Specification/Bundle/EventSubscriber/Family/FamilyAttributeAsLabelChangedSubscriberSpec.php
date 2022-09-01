<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family\FamilyAttributeAsLabelChangedSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\FindAttributeCodeAsLabelForFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyAttributeAsLabelChangedSubscriberSpec extends ObjectBehavior
{
    function let(FindAttributeCodeAsLabelForFamilyInterface $attributeCodeAsLabelForFamily, Client $esClient)
    {
        $this->beConstructedWith($attributeCodeAsLabelForFamily, $esClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyAttributeAsLabelChangedSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'storeFamilyCodeIfNeeded',
            StorageEvents::POST_SAVE => 'triggerFamilyRelatedProductsReindexation',
        ]);
    }

    function it_detects_that_the_attribute_code_as_label_of_the_family_changed_on_pre_save_and_run_es_request(
        $attributeCodeAsLabelForFamily,
        $esClient,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $family->getId()->willReturn(1);
        $family->getCode()->willReturn('test-family');
        $attribute->getCode()->willReturn('sku');
        $family->getAttributeAsLabel()->willReturn($attribute);
        $event->getSubject()->willReturn($family);

        $attributeCodeAsLabelForFamily->execute('test-family')->willReturn('name');

        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.label = ctx._source.values[params.attributeAsLabel]",
                'params' => ['attributeAsLabel' => sprintf('%s-text', 'sku')],
            ],
            'query' => [
                'term' => ['family.code' => 'test-family']
            ]
        ])->shouldBeCalled();

        $this->storeFamilyCodeIfNeeded($event);
        $this->triggerFamilyRelatedProductsReindexation($event);
    }

    function it_detects_it_does_not_need_to_run_es_request(
        $attributeCodeAsLabelForFamily,
        $esClient,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $family->getId()->willReturn(1);
        $family->getCode()->willReturn('test-family');
        $attribute->getCode()->willReturn('name');
        $family->getAttributeAsLabel()->willReturn($attribute);
        $event->getSubject()->willReturn($family);

        $attributeCodeAsLabelForFamily->execute('test-family')->willReturn('name');

        $esClient->updateByQuery()->shouldNotBeCalled();

        $this->storeFamilyCodeIfNeeded($event);
        $this->triggerFamilyRelatedProductsReindexation($event);
    }
}
