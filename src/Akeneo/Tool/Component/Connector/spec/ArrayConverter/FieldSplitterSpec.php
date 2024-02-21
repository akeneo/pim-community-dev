<?php

namespace spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter;

class FieldSplitterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FieldSplitter::class);
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
