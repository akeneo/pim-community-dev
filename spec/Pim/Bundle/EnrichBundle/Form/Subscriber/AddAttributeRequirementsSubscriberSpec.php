<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class AddAttributeRequirementsSubscriberSpec extends ObjectBehavior
{
    function let(ChannelManager $channelManager, ChannelInterface $printChannel, ChannelInterface $ecommerceChannel)
    {
        $this->beConstructedWith($channelManager);
        $channelManager->getChannels()->willReturn([$printChannel, $ecommerceChannel]);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('\Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'form.pre_set_data'  => 'preSetData',
            'form.post_set_data' => 'postSetData'
        ]);
    }

    function it_does_not_process_the_pre_set_data_if_event_is_not_about_a_family(
        ProductInterface $computer,
        FormEvent $event
    ) {
        $event->getData()->willReturn($computer);
        $computer->getAttributes()->shouldNotBeCalled();

        $this->preSetData($event)->shouldReturn(null);
    }

    function it_merges_missing_attribute_requirements_to_existing_one_before_set_data(
        FormEvent $event,
        FamilyInterface $computerFamily,
        AttributeRequirementInterface $descriptionRequirement,
        AttributeInterface $nameAttribute
    ) {
        $event->getData()->willReturn($computerFamily);

        $computerFamily->getAttributes()->willReturn([$nameAttribute]);

        $computerFamily->getAttributeRequirementKey(Argument::any())->willReturn('print_name');
        $computerFamily->getIndexedAttributeRequirements()->willReturn([
            'print_description'     => $descriptionRequirement,
            'ecommerce_description' => $descriptionRequirement
        ]);
        $computerFamily->setAttributeRequirements(Argument::size(3))->shouldBeCalled();

        $this->preSetData($event)->shouldReturn(null);
    }

    function it_does_not_process_the_post_set_data_if_event_is_not_about_a_family(
        ProductInterface $computer,
        FormEvent $event
    ) {
        $event->getData()->willReturn($computer);
        $event->getForm()->shouldNotBeCalled();

        $this->postSetData($event)->shouldReturn(null);
    }

    function it_removes_identifier_attributes_from_form_fields_and_make_sure_they_are_always_required_after_set_data(
        FormEvent $event,
        FamilyInterface $computerFamily,
        FormInterface $familyForm,
        FormInterface $indexedAttributeRequirementsForm,
        AttributeRequirementInterface $descriptionRequirement,
        AttributeRequirementInterface $skuRequirement,
        AttributeInterface $descriptionAttribute,
        AttributeInterface $skuAttribute
    ) {
        $event->getData()->willReturn($computerFamily);
        $event->getForm()->willReturn($familyForm);

        $computerFamily->getIndexedAttributeRequirements()->willReturn([
            'print_description'     => $descriptionRequirement,
            'ecommerce_description' => $descriptionRequirement,
            'print_sku'             => $skuRequirement,
            'ecommerce_sku'         => $skuRequirement
        ]);

        $descriptionRequirement->getAttribute()->willReturn($descriptionAttribute);
        $descriptionAttribute->getAttributeType()->willReturn('pim_catalog_textarea');

        $skuRequirement->getAttribute()->willReturn($skuAttribute);
        $skuAttribute->getAttributeType()->willReturn('pim_catalog_identifier');

        $descriptionRequirement->setRequired(Argument::any())->shouldNotBeCalled();
        $skuRequirement->setRequired(Argument::any())->shouldBeCalledTimes(2);

        $familyForm->get('indexedAttributeRequirements')->willReturn($indexedAttributeRequirementsForm);
        $indexedAttributeRequirementsForm->remove('print_description')->shouldNotBeCalled();
        $indexedAttributeRequirementsForm->remove('ecommerce_description')->shouldNotBeCalled();
        $indexedAttributeRequirementsForm->remove('print_sku')->shouldBeCalled();
        $indexedAttributeRequirementsForm->remove('ecommerce_sku')->shouldBeCalled();

        $this->postSetData($event)->shouldReturn(null);
    }
}
