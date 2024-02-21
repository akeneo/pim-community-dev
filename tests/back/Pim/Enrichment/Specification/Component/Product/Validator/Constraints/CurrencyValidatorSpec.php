<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CurrencyValidatorSpec extends ObjectBehavior
{
    function let(FindActivatedCurrenciesInterface $findActivatedCurrencies, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($findActivatedCurrencies);
        $this->initialize($context);
    }

    function it_validates_price_attribute(
        FindActivatedCurrenciesInterface $findActivatedCurrencies,
        $context,
        Currency $constraint,
        ProductPriceInterface $price
    ) {
        $price->getCurrency()->willReturn('EUR');
        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD']);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($price, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_currency_does_not_exists(
        FindActivatedCurrenciesInterface $findActivatedCurrencies,
        $context,
        Currency $constraint,
        ProductPriceInterface $price,
        ConstraintViolationBuilderInterface $violation,
        PriceCollectionValueInterface $priceCollectionValue
    ) {

        $priceCollectionValue->getAttributeCode()->willReturn('attribute_code');
        $context->getObject()->willReturn($priceCollectionValue);

        $price->getCurrency()->willReturn('CHF');
        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD']);
        $context->buildViolation(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->atPath('currency')->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Currency::CURRENCY)->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($price, $constraint)->shouldReturn(null);
    }
}
