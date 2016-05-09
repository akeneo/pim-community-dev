<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeInterface;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class AddAttributeTypeRelatedFieldsSubscriberSpec extends ObjectBehavior
{
    function let(
        AttributeTypeRegistry $attTypeRegistry,
        SecurityFacade $securityFacade,
        AttributeGroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith($attTypeRegistry, $securityFacade, $groupRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber');
    }

    function it_should_extend_add_attribute_type_related_field_subscriber()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber');
    }

    function it_customizes_the_attribute_form_type(
        $attTypeRegistry,
        $securityFacade,
        FormEvent $event,
        AttributeInterface $attribute,
        AttributeTypeInterface $attributeText,
        FormFactoryInterface $factory,
        FormInterface $form
    ) {
        $this->setFactory($factory);

        $event->getData()->willReturn($attribute);

        $attribute->getId()->willReturn(null);
        $attribute->getAttributeType()->willReturn('pim_text');

        $securityFacade->isGranted('pim_enrich_attributegroup_add_attribute')->willReturn(true);

        $attTypeRegistry->get('pim_text')->willReturn($attributeText);
        $attributeText->buildAttributeFormTypes($factory, $attribute)->willReturn([]);

        $event->getForm()->willReturn($form);
        $form->add('isDisplayable', 'switch')->shouldBeCalled();

        $this->preSetData($event);
    }
}
