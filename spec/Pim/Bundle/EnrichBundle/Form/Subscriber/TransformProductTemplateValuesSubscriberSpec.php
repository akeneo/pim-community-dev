<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TransformProductTemplateValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($normalizer, $denormalizer, $localeResolver);
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
        $denormalizer,
        $localeResolver,
        FormEvent $event,
        ProductTemplateInterface $template,
        ProductValueInterface $value
    ) {
        $event->getData()->willReturn($template);
        $template->getValuesData()->willReturn(['foo' => 'bar']);
        $collection = new ArrayCollection([$value]);

        $options = ['locale' => 'en_US', 'disable_grouping_separator' => true];
        $localeResolver->getCurrentLocale()->willReturn('en_US');

        $denormalizer->denormalize(['foo' => 'bar'], 'ProductValue[]', 'json', $options)
            ->willReturn($collection);

        $template->setValues($collection)->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_updates_product_template_normalized_values_after_submitting_the_form(
        $normalizer,
        $localeResolver,
        FormEvent $event,
        ProductTemplateInterface $template,
        ProductValueInterface $value
    ) {
        $event->getData()->willReturn($template);
        $template->getValues()->willReturn([$value]);

        $localeResolver->getCurrentLocale()->willReturn('en_US');

        $normalizer->normalize([$value], 'json', [
            'entity'                     => 'product',
            'locale'                     => 'en_US',
            'disable_grouping_separator' => true
        ])->willReturn(['foo' => 'bar']);

        $template->setValuesData(['foo' => 'bar'])->shouldBeCalled();

        $this->postSubmit($event);
    }
}
