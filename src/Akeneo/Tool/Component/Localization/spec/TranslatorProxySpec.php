<?php

namespace spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\TranslatorProxy;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorProxySpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TranslatorProxy::class);
    }

    function it_presents_translated_metric_unit(TranslatorInterface $translator)
    {
        $translator->trans('INCH', [], 'measures')->willReturn('Inch');

        $this->trans('INCH', ['domain' => 'measures'])->shouldReturn('Inch');
    }
}
