<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistry;
use Prophecy\Argument;

class ReferenceDataFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $qb,
        AttributeValidatorHelper $attrValidatorHelper,
        ConfigurationRegistry $registry
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $registry,
            ['IN', 'EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->getCode()->willReturn('color_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $value = [1];
        $qb->innerJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb
            ->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                Argument::any()
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', $value);
    }

    function it_adds_an_empty_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->getCode()->willReturn('color_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb->leftJoin(Argument::any(), Argument::any())->shouldBeCalled();
        $qb->andWhere(Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_valid_array(AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(1);
        $attribute->getCode()->willReturn('color_code');

        $value = 'string';
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected(1, 'filter', 'reference_data', $value)
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['foo'];
        $this->shouldThrow(
            InvalidArgumentException::numericExpected(1, 'filter', 'reference_data', 'string')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);
    }
}
