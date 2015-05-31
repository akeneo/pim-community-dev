<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Currency;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

class CurrencyValidatorSpec extends ObjectBehavior
{
    function let(CurrencyManager $currencyManager, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($currencyManager);
        $this->initialize($context);
    }

    function it_validates_price_attribute(
        $currencyManager,
        $context,
        Currency $constraint,
        ProductPriceInterface $price
    ) {
        $price->getCurrency()->willReturn('EUR');
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);
        $context->addViolationAt(Argument::cetera())->shouldNotBeCalled();

        $this->validate($price, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_currency_does_not_exists(
        $currencyManager,
        $context,
        Currency $constraint,
        ProductPriceInterface $price
    ) {
        $price->getCurrency()->willReturn('CHF');
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);
        $context->addViolationAt('currency', Argument::any())->shouldBeCalled();

        $this->validate($price, $constraint)->shouldReturn(null);
    }
}
