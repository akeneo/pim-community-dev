<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family\FamilyAttributeAsLabelChangedSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\FindAttributeCodeAsLabelForFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyAttributeAsLabelChangedSubscriberSpec extends ObjectBehavior
{
    function let(FindAttributeCodeAsLabelForFamilyInterface $attributeCodeAsLabelForFamily, Client $esClient)
    {
        $attributeCodeAsLabelForFamily->execute(Argument::type('string'))->willReturn('sku');
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
        Client $esClient,
        FamilyInterface $family,
        AttributeInterface $attribute,
    ) {
        $family->getId()->willReturn(1);
        $family->getCode()->willReturn('test-family');
        $attribute->getCode()->willReturn('name');
        $family->getAttributeAsLabel()->willReturn($attribute);
        $event = new GenericEvent($family->getWrappedObject());

        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.label = ctx._source.values[params.attributeAsLabel]",
                'params' => ['attributeAsLabel' => sprintf('%s-text', 'name')],
            ],
            'query' => [
                'term' => ['family.code' => 'test-family']
            ]
        ])->shouldBeCalled();

        $this->storeFamilyCodeIfNeeded($event);
        $this->triggerFamilyRelatedProductsReindexation($event);
    }

    function it_detects_it_does_not_need_to_run_es_request(
        Client $esClient,
        FamilyInterface $family,
        AttributeInterface $attribute,
    ) {
        $family->getId()->willReturn(1);
        $family->getCode()->willReturn('test-family');
        $attribute->getCode()->willReturn('sku');
        $family->getAttributeAsLabel()->willReturn($attribute);
        $event = new GenericEvent($family->getWrappedObject());

        $esClient->updateByQuery(Argument::any())->shouldNotBeCalled();

        $this->storeFamilyCodeIfNeeded($event);
        $this->triggerFamilyRelatedProductsReindexation($event);
    }

    function it_does_not_trigger_reindexation_for_a_new_family(
        Client $client,
        FamilyInterface $family,
    ) {
        $family->getId()->willReturn(null);
        $event = new GenericEvent($family);

        $client->updateByQuery(Argument::any())->shouldNotBeCalled();

        $this->storeFamilyCodeIfNeeded($event);
        $this->triggerFamilyRelatedProductsReindexation($event);
    }

    function it_only_reindexes_related_products_once_per_impacted_family(
        Client $esClient,
        FamilyInterface $family1,
        FamilyInterface $family2,
        FamilyInterface $family3,
        AttributeInterface $sku,
        AttributeInterface $name,
    ) {
        $sku->getCode()->willReturn('sku');
        $name->getCode()->willReturn('name');

        $family1->getId()->willReturn(1);
        $family1->getCode()->willReturn('family1');
        $family1->getAttributeAsLabel()->willReturn($name);
        $family2->getId()->willReturn(2);
        $family2->getCode()->willReturn('family2');
        $family2->getAttributeAsLabel()->willReturn($sku);
        $family3->getId()->willReturn(3);
        $family3->getCode()->willReturn('family3');
        $family3->getAttributeAsLabel()->willReturn($name);

        $esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.label = ctx._source.values[params.attributeAsLabel]",
                    'params' => ['attributeAsLabel' => sprintf('%s-text', 'name')],
                ],
                'query' => [
                    'term' => ['family.code' => 'family1']
                ]
            ]
        )->shouldBeCalledOnce();

        $esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.label = ctx._source.values[params.attributeAsLabel]",
                    'params' => ['attributeAsLabel' => sprintf('%s-text', 'sku')],
                ],
                'query' => [
                    'term' => ['family.code' => 'family2']
                ]
            ]
        )->shouldNotBeCalled();

        $esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.label = ctx._source.values[params.attributeAsLabel]",
                    'params' => ['attributeAsLabel' => sprintf('%s-text', 'name')],
                ],
                'query' => [
                    'term' => ['family.code' => 'family3']
                ]
            ]
        )->shouldBeCalledOnce();


        $this->storeFamilyCodeIfNeeded(new GenericEvent($family1->getWrappedObject()));
        $this->storeFamilyCodeIfNeeded(new GenericEvent($family2->getWrappedObject()));
        $this->storeFamilyCodeIfNeeded(new GenericEvent($family3->getWrappedObject()));
        $this->triggerFamilyRelatedProductsReindexation(new GenericEvent($family1->getWrappedObject()));
        $this->triggerFamilyRelatedProductsReindexation(new GenericEvent($family2->getWrappedObject()));
        $this->triggerFamilyRelatedProductsReindexation(new GenericEvent($family3->getWrappedObject()));
    }
}
