<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;

class InvalidOperatorExceptionSpec extends ObjectBehavior
{
    function it_creates_a_not_scalar_exception()
    {
        $exception = InvalidOperatorException::scalarExpected(
            ['=', '!='],
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
            ['value']
        );

        $this->beConstructedWith(
            ['=', '!='],
            ['value'],
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
            'Only scalar values are allowed for operators =, !=, "array" given.',
            InvalidOperatorException::SCALAR_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getOperators()->shouldReturn($exception->getOperators());
        $this->getValue()->shouldReturn($exception->getValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_array_exception()
    {
        $exception = InvalidOperatorException::arrayExpected(
            ['BETWEEN'],
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
            'value'
        );

        $this->beConstructedWith(
            ['BETWEEN'],
            'value',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
            'Only array values are allowed for operators BETWEEN, "string" given.',
            InvalidOperatorException::ARRAY_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getOperators()->shouldReturn($exception->getOperators());
        $this->getValue()->shouldReturn($exception->getValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_supported_exception()
    {
        $exception = InvalidOperatorException::notSupported(
            'other',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition'
        );

        $this->beConstructedWith(
            ['other'],
            null,
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
            'Operator "other" is not supported',
            InvalidOperatorException::NOT_SUPPORTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getOperators()->shouldReturn($exception->getOperators());
        $this->getValue()->shouldReturn($exception->getValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }
}
