<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
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
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_metric_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getType()->willReturn(Argument::any());
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

        $value = ['amount' => 16, 'unit' => 'CENTIMETER'];
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

    function it_adds_a_not_empty_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute, Expr $expr)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('metric');
        $attribute->getCode()->willReturn('metric_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn($expr);

        $qb->leftJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb->leftJoin(Argument::any(), Argument::any())->shouldBeCalled();
        $qb->andWhere(Argument::any())->shouldBeCalled();

        $expr->isNotNull(Argument::any())->shouldBeCalled()->willReturn('metric.base_data IS NOT NULL');

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_valid_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('metric_code');

        $value = ['unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'metric_code',
                'amount',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['amount' => 459];
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'metric_code',
                'unit',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['amount' => 'foo', 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'metric_code',
                'key "amount" has to be a numeric, "string" given',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['amount' => 132, 'unit' => 42];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'metric_code',
                'key "unit" has to be a string, "integer" given',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);
    }

    function it_throws_an_exception_if_value_had_not_a_valid_unit($measureManager, AttributeInterface $attribute)
    {
        $attribute->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);

        $attribute->getCode()->willReturn('metric_code');
        $value = ['amount' => 132, 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'metric_code',
                'unit',
                'The unit does not exist in the attribute\'s family "length"',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
                'foo'
            )
        )->during('addAttributeFilter', [$attribute, '=', $value]);
    }
}
