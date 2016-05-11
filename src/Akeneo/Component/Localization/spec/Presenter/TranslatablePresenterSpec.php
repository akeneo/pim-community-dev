<?php

namespace spec\Akeneo\Component\Localization\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class TranslatablePresenterSpec extends ObjectBehavior
{
    public function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator, ['pim_catalog_metric']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Localization\Presenter\TranslatablePresenter');
    }

    function it_is_a_presenter()
    {
        $this->shouldImplement('Akeneo\Component\Localization\Presenter\PresenterInterface');
    }

    function it_supports_metrics()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_presents_translated_metric_unit($translator)
    {
        $translator->trans('INCH', [], 'measures')->willReturn('Inch');

        $this->present('INCH', ['domain' => 'measures'])->shouldReturn('Inch');
    }
}
