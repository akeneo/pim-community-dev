<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class StringFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_identifier'],
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\StringFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']);
        $this->supportsOperator('ENDS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_starts_with_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^My Sku/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_ends_with_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku$/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_contains_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'CONTAINS', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_does_not_contain_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^((?!My Sku).)*$/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'DOES NOT CONTAIN', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');

        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'string', gettype(123)))
            ->during('addAttributeFilter', [$attribute, '=', 123, null, null, ['field' => 'attributeCode']]);
    }
}
