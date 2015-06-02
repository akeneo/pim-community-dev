<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MetricFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $qb,
        AttributeValidatorHelper $attrValidatorHelper,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $measureManager,
            $measureConverter,
            ['pim_catalog_metric'],
            ['<', '<=', '=', '>=', '>', 'EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_metric_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query(
        $qb,
        $measureManager,
        $measureConverter,
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('metric');
        $attribute->getCode()->willReturn('metric_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $value = ['data' => 16, 'unit' => 'CENTIMETER'];
        $attribute->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 16)->willReturn(0.16);

        $qb->innerJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb
            ->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                Argument::any()
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', $value);
    }

    function it_adds_an_empty_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('metric');
        $attribute->getCode()->willReturn('metric_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb->leftJoin(Argument::any(), Argument::any())->shouldBeCalled();
        $qb->andWhere(Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_valid_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('metric_code');

        $value = ['unit' => 'foo'];
        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('metric_code', 'data', 'filter', 'metric', print_r($value, true))
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['data' => 459];
        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('metric_code', 'unit', 'filter', 'metric', print_r($value, true))
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['data' => 'foo', 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidArgumentException::arrayNumericKeyExpected('metric_code', 'data', 'filter', 'metric', 'string')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['data' => 132, 'unit' => 42];
        $this->shouldThrow(
            InvalidArgumentException::arrayStringKeyExpected('metric_code', 'unit', 'filter', 'metric', 'integer')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);
    }

    function it_throws_an_exception_if_value_had_not_a_valid_unit($measureManager, AttributeInterface $attribute)
    {
        $attribute->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);

        $attribute->getCode()->willReturn('metric_code');
        $value = ['data' => 132, 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidArgumentException::arrayInvalidKey(
                'metric_code',
                'unit',
                'The unit does not exist in the attribute\'s family "length"',
                'filter',
                'metric',
                'foo'
            )
        )->during('addAttributeFilter', [$attribute, '=', $value]);
    }
}
