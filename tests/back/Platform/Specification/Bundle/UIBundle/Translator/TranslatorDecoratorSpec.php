<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Translator;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class TranslatorDecoratorSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_translates($translator)
    {
        $translator->trans('to_translate', [], null, null)->willReturn('It is translated.');

        $this->trans('to_translate')->shouldReturn('It is translated.');
    }

    function it_translates_choice($translator)
    {
        $key = 'something_to_translate';
        $translator
            ->transChoice($key, 2, [], null, null)
            ->willReturn('2 affected products');

        $this
            ->transChoice($key, 2)
            ->shouldReturn('2 affected products');
    }

    /**
     * @see PIM-8334
     *
     * @param $translator
     */
    function it_returns_the_translation_key_and_the_number_if_it_can_not_translate_the_choice($translator)
    {
        $brokenKey = 'something_to_translate';
        $translator
            ->transChoice($brokenKey, 2, [], null, null)
            ->willThrow(new \Exception('Can not translate because the key is broken.'));

        $this
            ->transChoice($brokenKey, 2)
            ->shouldReturn('something_to_translate: 2');
    }

    function it_sets_locale($translator)
    {
        $translator->setLocale('en_US')->shouldBeCalled();

        $this->setLocale('en_US');
    }

    function it_gets_locale($translator)
    {
        $translator->getLocale()->willReturn('en_US');

        $this->getLocale()->shouldReturn('en_US');
    }
}
