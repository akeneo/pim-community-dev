<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant\FieldSplitter;

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
        $this->splitFieldName('variant-attributes_1')->shouldReturn(['variant-attributes', '1']);
        $this->splitFieldName('variant-attributes_2')->shouldReturn(['variant-attributes', '2']);
        $this->splitFieldName('variant-axes_1')->shouldReturn(['variant-axes', '1']);
        $this->splitFieldName('variant-axes_2')->shouldReturn(['variant-axes', '2']);
        $this->splitFieldName('description-en_US-mobile')->shouldReturn(['description', 'en_US', 'mobile']);
        $this->splitFieldName('')->shouldReturn([]);
    }
}
