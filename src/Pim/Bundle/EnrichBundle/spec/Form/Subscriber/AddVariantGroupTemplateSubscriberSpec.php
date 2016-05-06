<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class AddVariantGroupTemplateSubscriberSpec extends ObjectBehavior
{
    function let(UserContext $userContext)
    {
        $this->beConstructedWith($userContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Subscriber\AddVariantGroupTemplateSubscriber');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_set_data_form_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['form.pre_set_data' => 'preSetData']);
    }

    function it_adds_product_template_to_group_form_if_the_group_type_is_variant(
        $userContext,
        FormEvent $event,
        FormInterface $form,
        Group $group,
        GroupType $type
    ) {
        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $event->getData()->willReturn($group);
        $event->getForm()->willReturn($form);

        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $form->add(
            'productTemplate',
            'pim_enrich_product_template',
            [
                'currentLocale' => 'en_US'
            ]
        )->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_does_nothing_if_the_group_type_is_not_variant(FormEvent $event, Group $group, GroupType $type)
    {
        $event->getData()->willReturn($group);

        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);

        $event->getForm()->shouldNotBeCalled();

        $this->preSetData($event);
    }
}
