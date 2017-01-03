<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\Currency;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CurrencyValidatorSpec extends ObjectBehavior
{
    function let(CurrencyRepositoryInterface $currencyRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($currencyRepository);
        $this->initialize($context);
    }

    function it_validates_price_attribute(
        $currencyRepository,
        $context,
        Currency $constraint,
        ProductPriceInterface $price
    ) {
        $price->getCurrency()->willReturn('EUR');
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($price, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_currency_does_not_exists(
        $currencyRepository,
        $context,
        Currency $constraint,
        ProductPriceInterface $price,
        ConstraintViolationBuilderInterface $violation
    ) {
        $price->getCurrency()->willReturn('CHF');
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $context->buildViolation(Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->atPath('currency')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($price, $constraint)->shouldReturn(null);
    }
}
