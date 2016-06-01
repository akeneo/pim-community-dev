<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

use PhpSpec\ObjectBehavior;

class FieldSplitterSpec extends ObjectBehavior
{
    function it_split_prices()
    {
        $this->splitPrices('120 EUR,  125 USD')->shouldReturn(['120 EUR','125 USD']);
        $this->splitPrices('120,25 EUR, 125 USD')->shouldReturn(['120,25 EUR','125 USD']);
        $this->splitPrices('120,25 EUR, 125,50 USD')->shouldReturn(['120,25 EUR','125,50 USD']);
        $this->splitPrices('120.25 EUR, 125,50 USD')->shouldReturn(['120.25 EUR','125,50 USD']);
        $this->splitPrices('120.25 EUR, -125,50 USD')->shouldReturn(['120.25 EUR','-125,50 USD']);
        $this->splitPrices(' EUR, USD')->shouldReturn(['EUR','USD']);
        $this->splitPrices('EUR,USD')->shouldReturn(['EUR','USD']);
        $this->splitPrices('120 EUR,125,50 USD,150.52 OOO, RRR, 1864|44 MMM')
            ->shouldReturn(['120 EUR','125,50 USD','150.52 OOO','RRR','1864|44 MMM']);
        $this->splitPrices('')->shouldReturn([]);
        $this->splitPrices('invalid')->shouldReturn(['invalid']);
        $this->splitPrices('120.25 EUR,  gruik#125 USD')->shouldReturn(['120.25 EUR','gruik#125 USD']);
    }

    function it_split_collection()
    {
        $this->splitCollection('boots, sandals')->shouldReturn(['boots','sandals']);
        $this->splitCollection('boots, sandals,  tshirt')->shouldReturn(['boots','sandals', 'tshirt']);
        $this->splitCollection('boots')->shouldReturn(['boots']);
        $this->splitCollection('')->shouldReturn([]);
    }

    function it_split_unit_value()
    {
        $this->splitUnitValue('10 EUR')->shouldReturn(['10', 'EUR']);
        $this->splitUnitValue('10 METER')->shouldReturn(['10', 'METER']);
        $this->splitUnitValue('10METER')->shouldReturn(['10METER']);
        $this->splitUnitValue('')->shouldReturn([]);
    }

    function it_split_field_name()
    {
        $this->splitFieldName('description-en_US-mobile')->shouldReturn(['description', 'en_US', 'mobile']);
        $this->splitFieldName('description-en_US')->shouldReturn(['description', 'en_US']);
        $this->splitFieldName('description')->shouldReturn(['description']);
        $this->splitFieldName('description--mobile')->shouldReturn(['description', '', 'mobile']);
        $this->splitFieldName('description--')->shouldReturn(['description', '', '']);
        $this->splitFieldName('')->shouldReturn([]);
    }
}
