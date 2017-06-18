<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionsFilter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class OptionsFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith($attrValidatorHelper, $objectIdResolver, ['pim_catalog_multiselect'], ['IN', 'EMPTY', 'NOT EMPTY', 'NOT IN']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY', 'NOT EMPTY', 'NOT IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_multi_select_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAliases()->willReturn(['r']);
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->innerJoin(
            Argument::any(),
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);

        $this->addAttributeFilter($attribute, 'IN', ['22', '42'], null, null, ['field' => 'options_code.id']);
    }

    function it_adds_an_empty_filter_to_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAliases()->willReturn(['r']);
        $qb->expr()->willReturn($expr);

        $expr->isNull(Argument::any())->shouldBeCalled()->willReturn('filteroptions_code.option IS NULL');

        $qb->leftJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->leftJoin(Argument::any(), Argument::any())->willReturn($qb);
        $qb
            ->andWhere(
                'filteroptions_code.option IS NULL'
            )
            ->shouldBeCalled()
        ;

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'options_code.id']);
    }

    function it_adds_a_not_empty_filter_to_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAliases()->willReturn(['r']);
        $qb->expr()->willReturn($expr);

        $expr->isNotNull(Argument::any())->shouldBeCalled()->willReturn('filteroptions_code.option IS NOT NULL');

        $qb->leftJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->leftJoin(Argument::any(), Argument::any())->willReturn($qb);
        $qb->andWhere('filteroptions_code.option IS NOT NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null, null, null, ['field' => 'options_code.id']);
    }

    function it_adds_a_not_in_filter_to_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        EntityManager $em,
        QueryBuilder $notInQb,
        Expr $expr,
        Expr\Func $notInFunc,
        Expr\Func $inFunc,
        Expr\Func $whereFunc
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAliases()->willReturn(['r']);
        $expr->notIn(Argument::containingString('filterOoptions_code'), [10, 12])
            ->shouldBeCalled()
            ->willReturn($notInFunc);
        $qb->innerJoin(Argument::cetera())->willReturn($qb);

        $qb->getEntityManager()->willReturn($em);
        $qb->getRootEntities()->willReturn(['ProductClassName']);
        $em->createQueryBuilder()->willReturn($notInQb);
        $notInQb->select(Argument::containingString('.id'))->shouldBeCalled()->willReturn($notInQb);
        $notInQb->from(
            'ProductClassName',
            Argument::any(),
            Argument::containingString('.id')
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->getRootAliases()->willReturn(['ep']);
        $notInQb->innerJoin(
            'ep.values',
            Argument::containingString('filteroptions_code'),
            'WITH',
            Argument::any()
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->innerJoin(
            Argument::containingString('filteroptions_code'),
            Argument::containingString('filterOoptions_code')
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->expr()->willReturn($expr);
        $expr->in(Argument::containingString('.id'), [10, 12])
            ->shouldBeCalled()
            ->willReturn($inFunc);
        $notInQb->where($inFunc)->shouldBeCalled();
        $notInQb->getDQL()->willReturn('excluded products DQL');

        $qb->expr()->willReturn($expr);
        $expr->notIn('r.id', 'excluded products DQL')
            ->shouldBeCalled()
            ->willReturn($whereFunc);
        $qb->andWhere($whereFunc)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT IN', [10, 12], null, null, ['field' => 'options_code.id']);
    }

    function it_throws_an_exception_if_value_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('options_code');
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected(
            'options_code',
            OptionsFilter::class,
            'WRONG'
        ))->during('addAttributeFilter', [$attribute, 'IN', 'WRONG', null, null, ['field' => 'options_code.id']]);
    }

    function it_throws_an_exception_if_the_content_of_value_are_not_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('options_code');
        $this->shouldThrow(InvalidPropertyTypeException::numericExpected(
            'options_code',
            OptionsFilter::class,
            'not numeric'
        ))->during('addAttributeFilter', [$attribute, 'IN', [123, 'not numeric'], null, null, ['field' => 'options_code.id']]);
    }
}
