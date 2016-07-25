<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;

class ReferenceDataFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $qb,
        ConfigurationRegistryInterface $registry,
        ReferenceDataIdResolver $idResolver
    ) {
        $this->beConstructedWith(
            $registry,
            $idResolver,
            ['IN', 'EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_ids_to_the_query(
        $qb,
        AttributeInterface $attribute
    ) {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->getCode()->willReturn('color');

        $qb->getRootAliases()->willReturn(['r']);
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb
            ->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                Argument::any()
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', [1, 2], null, null, ['field' => 'color']);
    }

    function it_adds_a_filter_with_codes_to_the_query(
        $qb,
        $idResolver,
        AttributeInterface $attribute
    ) {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->getCode()->willReturn('color');

        $idResolver->resolve('color', ['red', 'blue'])->willReturn([1, 2]);

        $qb->getRootAliases()->willReturn(['r']);
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb
            ->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                Argument::any()
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['red', 'blue'], null, null, ['field' => 'color.code']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->getCode()->willReturn('color');

        $qb->getRootAliases()->willReturn(['r']);
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin('r.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $qb->leftJoin(Argument::any(), Argument::any())->shouldBeCalled();
        $qb->andWhere(Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'color']);
    }

    function it_throws_an_exception_if_value_is_not_a_valid_array(AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(1);
        $attribute->getCode()->willReturn('color');

        $value = 'string';
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('color', 'filter', 'reference_data', $value)
        )
            ->during('addAttributeFilter', [$attribute, '=', $value, null, null, ['field' => 'color']]);

        $value = ['foo'];
        $this->shouldThrow(
            InvalidArgumentException::numericExpected('color', 'filter', 'reference_data', 'string')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value, null, null, ['field' => 'color']]);
    }
}
