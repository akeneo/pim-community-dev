<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ReferenceData;

use PhpSpec\ObjectBehavior;

class MethodNameGuesserSpec extends ObjectBehavior
{
    function it_guesses_method_name()
    {
        $this::guess('set', 'colors', true)
            ->shouldReturn('setColor');

        $this::guess('add', 'amazingCats', true)
            ->shouldReturn('addAmazingCat');

        $this::guess('get', 'battlecruisers')
            ->shouldReturn('getBattlecruisers');

        $this::guess('remove', 'tableFeet', true)
            ->shouldReturn('removeTableFoot');

        $this::guess('get', 'carWheels', true)
            ->shouldReturn('getCarWheel');

        $this::guess('set', 'furBoots')
            ->shouldReturn('setFurBoots');

        $this::guess('add', 'currencies', true)
            ->shouldReturn('addCurrency');

        $this::guess('get', 'foreignCurrencies')
            ->shouldReturn('getForeignCurrencies');
    }

    function it_throws_an_exception_if_it_cannot_guess_singular_word()
    {
        $this->shouldThrow('LogicException')
            ->during('guess', ['set', 'creamses', true]);
    }
}
