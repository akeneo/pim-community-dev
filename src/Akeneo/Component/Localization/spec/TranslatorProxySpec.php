<?php

namespace spec\Akeneo\Component\Localization;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class TranslatorProxySpec extends ObjectBehavior
{
    public function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Localization\TranslatorProxy');
    }

    function it_presents_translated_metric_unit($translator)
    {
        $translator->trans('INCH', [], 'measures')->willReturn('Inch');

        $this->trans('INCH', ['domain' => 'measures'])->shouldReturn('Inch');
    }
}
