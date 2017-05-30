<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class OptionFilterSpec extends ObjectBehavior
{
    function let(
        Builder $qb,
        AttributeValidatorHelper $attrValidatorHelper,
        ObjectIdResolverInterface $objectIdResolver
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $objectIdResolver,
            ['pim_catalog_simpleselect'],
            ['IN', 'EMPTY', 'NOT EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {

        $this->shouldHaveType(
            'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionFilter')
        ;
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY', 'NOT EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_simple_select_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_an_in_filter_for_an_option_id_to_the_query($attrValidatorHelper, $qb, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->in([118, 270])->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', [118, 270], null, null, ['field' => 'option_code.id']);
    }

    function it_adds_an_in_filter_for_an_option_code_to_the_query(
        $attrValidatorHelper,
        $qb,
        $objectIdResolver,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $objectIdResolver->getIdsFromCodes('option', ['red', 'yellow'], Argument::type('Pim\Component\Catalog\Model\AttributeInterface'))
            ->shouldBeCalled()
            ->willReturn([118, 270]);

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->in([118, 270])->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['red', 'yellow'], null, null, ['field' => 'option_code.code']);
    }

    function it_adds_a_not_in_filter_for_an_option_id_to_the_query($attrValidatorHelper, $qb, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();
        $qb->notIn([118, 270])->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT IN', [118, 270], null, null, ['field' => 'option_code.id']);
    }

    function it_adds_a_not_in_filter_for_an_option_code_to_the_query(
        $attrValidatorHelper,
        $qb,
        $objectIdResolver,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $objectIdResolver->getIdsFromCodes('option', ['red', 'yellow'], Argument::type('Pim\Component\Catalog\Model\AttributeInterface'))
            ->shouldBeCalled()
            ->willReturn([118, 270]);

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();
        $qb->notIn([118, 270])->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT IN', ['red', 'yellow'], null, null, ['field' => 'option_code.code']);
    }

    function it_adds_an_empty_filter_for_an_option_id_to_the_query($attrValidatorHelper, $qb, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->exists(false)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'option_code.id']);
    }

    function it_adds_an_empty_filter_for_an_option_code_to_the_query($attrValidatorHelper, $qb, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->exists(false)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'option_code.code']);
    }

    function it_adds_a_not_empty_filter_for_an_option_id_to_the_query($attrValidatorHelper, $qb, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null, null, null, ['field' => 'option_code.id']);
    }

    function it_adds_a_not_empty_filter_for_an_option_code_to_the_query($attrValidatorHelper, $qb, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->field('normalizedData.option_code.id')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null, null, null, ['field' => 'option_code.code']);
    }

    function it_throws_an_exception_if_value_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected(
            'option_code',
            'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionFilter',
            'WRONG'
        ))
            ->during('addAttributeFilter', [$attribute, 'IN', 'WRONG', null, null, ['field' => 'option_code.id']]);
    }

    function it_throws_an_exception_if_the_content_of_value_are_not_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidPropertyTypeException::numericExpected(
            'option_code',
            'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionFilter',
            'not numeric'
        ))
            ->during('addAttributeFilter', [$attribute, 'IN', [123, 'not numeric'], null, null, ['field' => 'option_code.id']]);
    }
}
