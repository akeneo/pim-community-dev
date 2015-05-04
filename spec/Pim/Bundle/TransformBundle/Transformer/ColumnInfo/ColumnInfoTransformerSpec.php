<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\ColumnInfo;

use PhpSpec\ObjectBehavior;

class ColumnInfoTransformerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformer');
    }

    function it_is_a_column_transformer()
    {
        $this->shouldImplement('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface');
    }

    function it_transforms_arrays_of_labels_into_colum_info()
    {
        $labels = $this->transform('\stdClass', ['Description', 'Beschreibung']);
        $labels[0]->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo');
        $labels[1]->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo');
        $labels[0]->getLabel()->shouldReturn('Description');
        $labels[1]->getLabel()->shouldReturn('Beschreibung');
    }

    function it_transforms_single_labels_into_colum_info()
    {
        $label = $this->transform('\stdClass', 'Description');
        $label->getLabel()->shouldReturn('Description');
        $label->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo');
    }
}
