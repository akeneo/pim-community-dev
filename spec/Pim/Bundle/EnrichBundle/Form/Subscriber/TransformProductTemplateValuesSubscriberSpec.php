<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ProductTemplateValuesTransformer;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;

class TransformProductTemplateValuesSubscriberSpec extends ObjectBehavior
{
    function let(ProductTemplateValuesTransformer $transformer)
    {
        $this->beConstructedWith($transformer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_set_data_and_post_submit_form_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                'form.pre_set_data' => 'preSetData',
                'form.post_bind'    => 'postSubmit'
            ]
        );
    }

    function it_sets_denormalized_values_to_product_template_before_setting_form_data(
        $transformer,
        FormEvent $event,
        ProductTemplateInterface $template,
        ProductValueInterface $value
    ) {
        $event->getData()->willReturn($template);
        $template->getValuesData()->willReturn(['foo' => 'bar']);
        $transformer->transform(['foo' => 'bar'])->willReturn([$value]);

        $template->setValues([$value])->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_updates_product_template_normalized_values_after_submitting_the_form(
        $transformer,
        FormEvent $event,
        ProductTemplateInterface $template,
        ProductValueInterface $value
    ) {
        $event->getData()->willReturn($template);
        $template->getValues()->willReturn([$value]);
        $transformer->reverseTransform([$value])->willReturn(['foo' => 'bar']);

        $template->setValuesData(['foo' => 'bar'])->shouldBeCalled();

        $this->postSubmit($event);
    }
}
