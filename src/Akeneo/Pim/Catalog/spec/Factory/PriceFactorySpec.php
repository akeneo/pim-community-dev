<?php

namespace spec\Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Model\ProductPrice;

class PriceFactorySpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $currencyRepository)
    {
        $this->beConstructedWith($currencyRepository, ProductPrice::class);
    }

    function it_creates_a_price($currencyRepository, Currency $currency)
    {
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($currency);

        $price = $this->createPrice(42, 'EUR');

        $price->shouldReturnAnInstanceOf(ProductPrice::class);
        $price->__toString()->shouldBeEqualTo('42.00 EUR');
        $price->getCurrency()->shouldBeEqualTo('EUR');
        $price->getData()->shouldBeEqualTo(42);
    }

    function it_creates_a_metric_if_provided_data_is_null($currencyRepository, Currency $currency)
    {
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($currency);

        $price = $this->createPrice(null, 'EUR');

        $price->shouldReturnAnInstanceOf(ProductPrice::class);
        $price->__toString()->shouldBeEqualTo('');
        $price->getCurrency()->shouldBeEqualTo('EUR');
        $price->getData()->shouldBeEqualTo(null);
    }

    function it_creates_a_metric_if_provided_data_is_not_a_numeric($currencyRepository, Currency $currency)
    {
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($currency);

        $price = $this->createPrice('foobar', 'EUR');

        $price->shouldReturnAnInstanceOf(ProductPrice::class);
        $price->__toString()->shouldBeEqualTo('0.00 EUR');
        $price->getCurrency()->shouldBeEqualTo('EUR');
        $price->getData()->shouldBeEqualTo('foobar');
    }

    function it_throws_an_exception_if_provided_currency_code_does_not_exists($currencyRepository)
    {
        $currencyRepository->findOneByIdentifier('FOOBAR')->willReturn(null);

        $exception = InvalidPropertyException::validEntityCodeExpected(
            'currency',
            'code',
            'The currency does not exist',
            PriceFactory::class,
            'FOOBAR'
        );

        $this->shouldThrow($exception)->during('createPrice', [42, 'FOOBAR']);
    }
}
