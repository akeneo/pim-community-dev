<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\ColumnInfo;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\TransformBundle\Exception\ColumnLabelException;

class ColumnInfoSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo');
    }

    function it_is_a_column_interface()
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->shouldImplement('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
    }

    function it_provides_the_label()
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->getLabel()->shouldReturn('name-en_US-ecommerce');
    }

    function it_has_a_name()
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->getName()->shouldReturn('name');
    }

    function it_has_a_property_path(AttributeInterface $attribute)
    {
        $this->beConstructedWith('foo_name-en_US-ecommerce');
        $this->getPropertyPath()->shouldReturn('fooName');

        $this->setPropertyPath('newName');
        $this->getPropertyPath()->shouldReturn('newName');

        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()
            ->willReturn(AbstractAttributeType::BACKEND_TYPE_REF_DATA_OPTION);
        $attribute->getReferenceDataName()->willReturn('ref_name');
        $this->setAttribute($attribute);
        $this->getPropertyPath()->shouldReturn('ref_name');

        $attribute->getBackendType()
            ->willReturn(AbstractAttributeType::BACKEND_TYPE_BOOLEAN);
        $this->setAttribute($attribute);
        $this->getPropertyPath()
            ->shouldReturn(AbstractAttributeType::BACKEND_TYPE_BOOLEAN);
    }

    function it_has_a_locale(AttributeInterface $attribute)
    {
        $this->beConstructedWith('name-en_US');
        $this->getLocale()->shouldReturn(null);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn(null);
        $this->setAttribute($attribute);
        $this->getLocale()->shouldReturn('en_US');
    }

    function it_has_a_scope(AttributeInterface $attribute)
    {
        $this->beConstructedWith('name-ecommerce');
        $this->getLocale()->shouldReturn(null);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn(null);
        $this->setAttribute($attribute);
        $this->getScope()->shouldReturn('ecommerce');
    }

    function it_has_suffixes(AttributeInterface $attribute)
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->getSuffixes()->shouldReturn(['en_US', 'ecommerce']);
    }

    function it_has_an_attribute(AttributeInterface $attribute)
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->getAttribute()->shouldReturn(null);
        $this->setAttribute($attribute);
        $this->getAttribute()->shouldReturn($attribute);
    }

    function it_sets_a_null_attribute()
    {
        $this->beConstructedWith('name-en_US-ecommerce');
        $this->getAttribute()->shouldReturn(null);
        $this->setAttribute(null);
        $this->getAttribute()->shouldReturn(null);
    }

    function it_throws_an_exception_when_given_an_incorrect_label_length(AttributeInterface $attribute)
    {
        $this->beConstructedWith('name-en_US');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn(null);
        $this->shouldThrow(new ColumnLabelException(
            'The column "%column%" must contain a scope code',
                array('%column%' => 'name-en_US')
        ))->during('setAttribute', [$attribute]);
    }
}
