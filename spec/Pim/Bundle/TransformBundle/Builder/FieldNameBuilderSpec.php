<?php

namespace spec\Pim\Bundle\TransformBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver;

class FieldNameBuilderSpec extends ObjectBehavior
{
    function let(
        AssociationColumnsResolver $resolver,
        AttributeColumnInfoExtractor $extractor
    ) {
        $this->beConstructedWith($resolver, $extractor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Builder\FieldNameBuilder');
    }

    function it_resolves_association_fields($resolver)
    {
        $resolver->resolveAssociationColumns()->shouldBeCalled();
        $this->getAssociationFieldNames();
    }

    function it_resolves_attribute_info($extractor)
    {
        $extractor->extractColumnInfo('field')->shouldBeCalled();
        $this->extractAttributeFieldNameInfos('field');
    }
}
