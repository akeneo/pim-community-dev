<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class BooleanFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($attrValidatorHelper, ['pim_catalog_boolean'], ['enabled'], ['=', '!=']);
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BooleanFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query($qb)
    {
        $qb->field('normalizedData.enabled')->shouldBeCalled()->willReturn($qb);
        $qb->equals(true)->shouldBeCalled()->willReturn($qb);

        $this->addFieldFilter('enabled', '=', true, 'en_US', 'mobile');
    }

    function it_adds_a_not_equal_filter_on_a_field_in_the_query($qb)
    {
        $qb->field('normalizedData.enabled')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled()->willReturn($qb);
        $qb->notEqual(true)->shouldBeCalled();

        $this->addFieldFilter('enabled', '!=', true, 'en_US', 'mobile');
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('handmade');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $qb->field('normalizedData.handmade-en_US-mobile')->shouldBeCalled()->willReturn($qb);
        $qb->equals(true)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', true, 'en_US', 'mobile');
    }

    function it_adds_a_not_equal_filter_on_an_attribute_in_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('enabled');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $qb->field('normalizedData.enabled-en_US-mobile')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled()->willReturn($qb);
        $qb->notEqual(true)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '!=', true, 'en_US', 'mobile');
    }

    function it_throws_an_exception_if_value_is_not_a_boolean()
    {
        $this->shouldThrow(InvalidPropertyTypeException::booleanExpected(
            'enabled',
            'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BooleanFilter',
            'not a boolean'
        ))->during('addFieldFilter', ['enabled', '=', 'not a boolean']);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['enabled']);
    }

    function it_returns_supported_attributes()
    {
        $this->getAttributeTypes()->shouldReturn(['pim_catalog_boolean']);
    }
}
