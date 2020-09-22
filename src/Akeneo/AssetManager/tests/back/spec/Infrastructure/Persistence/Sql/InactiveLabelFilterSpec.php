<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql;

use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use PhpSpec\ObjectBehavior;

class InactiveLabelFilterSpec extends ObjectBehavior
{
    function let(FindActivatedLocalesInterface $activatedLocales)
    {
        $activatedLocales->findAll()->willReturn(['en_GB', 'en_US', 'de_DE', 'es_ES', 'fr_BE']);

        $this->beConstructedWith($activatedLocales);
    }

    function it_do_nothing_on_empty_array()
    {
        $this->filter([])->shouldReturn([]);
    }

    function it_filter_labels_when_locale_is_inactive()
    {
        $this->filter(['fr_FR' => 'mon label', 'en_US' => 'my label'])->shouldReturn(['en_US' => 'my label']);
        $this->filter(['fr_FR' => 'mon label', 'nl_NL' => 'mijn label'])->shouldReturn([]);
        $this->filter([
            'en_GB' => 'my label',
            'en_US' => 'my label',
            'de_DE' => 'mein Etikett',
            'es_ES' => 'mi etiqueta',
            'fr_BE' => 'mon label',
        ])->shouldReturn([
            'en_GB' => 'my label',
            'en_US' => 'my label',
            'de_DE' => 'mein Etikett',
            'es_ES' => 'mi etiqueta',
            'fr_BE' => 'mon label',
        ]);
    }

    function it_cache_activated_locales(FindActivatedLocalesInterface $activatedLocales)
    {
        $activatedLocales->findAll()->shouldBeCalledTimes(1);

        $this->filter(['fr_FR' => 'mon label', 'en_US' => 'my label'])->shouldReturn(['en_US' => 'my label']);
        $this->filter(['fr_FR' => 'mon label', 'en_US' => 'my label'])->shouldReturn(['en_US' => 'my label']);
    }
}
