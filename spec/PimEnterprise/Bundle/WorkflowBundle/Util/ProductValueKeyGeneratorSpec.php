<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Util;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Util\ProductValueKeyGenerator;

class ProductValueKeyGeneratorSpec extends ObjectBehavior
{
    function it_generates_value_key(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $this->generate($value)->shouldReturn('foo');
    }

    function it_generates_localizable_value_key(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);

        $this->generate($value)->shouldReturn('foo-fr_FR');
    }

    function it_generates_scopable_value_key(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getScope()->willReturn('print');
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);

        $this->generate($value)->shouldReturn('foo--print');
    }

    function it_generates_localizable_and_scopable_value_key(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getLocale()->willReturn('fr_FR');
        $value->getScope()->willReturn('print');
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $this->generate($value)->shouldReturn('foo-fr_FR-print');
    }

    function it_finds_the_code_part_of_a_key()
    {
        $this->getPart('foo', ProductValueKeyGenerator::CODE)->shouldReturn('foo');
        $this->getPart('foo-fr_FR', ProductValueKeyGenerator::CODE)->shouldReturn('foo');
        $this->getPart('foo-fr_FR-print', ProductValueKeyGenerator::CODE)->shouldReturn('foo');
        $this->getPart('foo--print', ProductValueKeyGenerator::CODE)->shouldReturn('foo');
    }

    function it_finds_the_locale_part_of_a_key()
    {
        $this->getPart('foo', ProductValueKeyGenerator::LOCALE)->shouldReturn(null);
        $this->getPart('foo-fr_FR', ProductValueKeyGenerator::LOCALE)->shouldReturn('fr_FR');
        $this->getPart('foo-fr_FR-print', ProductValueKeyGenerator::LOCALE)->shouldReturn('fr_FR');
        $this->getPart('foo--print', ProductValueKeyGenerator::LOCALE)->shouldReturn(null);
    }

    function it_finds_the_scope_part_of_a_key()
    {
        $this->getPart('foo', ProductValueKeyGenerator::SCOPE)->shouldReturn(null);
        $this->getPart('foo-fr_FR', ProductValueKeyGenerator::SCOPE)->shouldReturn(null);
        $this->getPart('foo-fr_FR-print', ProductValueKeyGenerator::SCOPE)->shouldReturn('print');
        $this->getPart('foo--print', ProductValueKeyGenerator::SCOPE)->shouldReturn('print');
    }

    function it_throws_exception_when_trying_to_access_unknown_part_of_a_key()
    {
        $e = new \InvalidArgumentException('Unknown key part "foo"');

        $this->shouldThrow($e)->duringGetPart(ProductValueKeyGenerator::CODE, 'foo');
    }
}
